<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminEmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_from_employee_admin(): void
    {
        $this->get(route('admin.employees.index'))
            ->assertRedirect(route('login'));
    }

    public function test_super_admin_can_open_employee_management_and_forms(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $employee = Employee::factory()->for($admin, 'updatedBy')->create(['name' => 'Pegawai Dukcapil']);

        $this->actingAs($admin)->get(route('admin.employees.index'))->assertSee('Pegawai Dukcapil');
        $this->actingAs($admin)->get(route('admin.employees.create'))->assertSee('Tambah pegawai');
        $this->actingAs($admin)->get(route('admin.employees.edit', $employee))->assertSee('Edit pegawai');
    }

    public function test_super_admin_can_create_employee_with_photo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->post(route('admin.employees.store'), $this->payload())
            ->assertRedirect(route('admin.employees.index'))
            ->assertSessionHas('status');

        $employee = Employee::query()->sole();
        Storage::disk('public')->assertExists($employee->photo_url);
        $this->assertSame('Siti Rahma', $employee->name);
        $this->assertSame($admin->id, $employee->updated_by);
        $this->assertTrue($employee->is_published);
        $this->assertDatabaseHas('audit_logs', ['action' => 'employee.created', 'user_id' => $admin->id]);
    }

    public function test_super_admin_can_update_employee_and_replace_old_photo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('employees/photos/old.jpg', 'image');
        $admin = User::factory()->superAdmin()->create();
        $employee = Employee::factory()->for($admin, 'updatedBy')->create(['photo_url' => 'employees/photos/old.jpg']);

        $this->actingAs($admin)
            ->put(route('admin.employees.update', $employee), $this->payload(['name' => 'Nama Diperbarui']))
            ->assertRedirect(route('admin.employees.index'));

        $employee->refresh();
        Storage::disk('public')->assertMissing('employees/photos/old.jpg');
        Storage::disk('public')->assertExists($employee->photo_url);
        $this->assertSame('Nama Diperbarui', $employee->name);
        $this->assertDatabaseHas('audit_logs', ['action' => 'employee.updated']);
    }

    public function test_super_admin_can_delete_employee_and_photo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('employees/photos/pegawai.jpg', 'image');
        $admin = User::factory()->superAdmin()->create();
        $employee = Employee::factory()->for($admin, 'updatedBy')->create(['photo_url' => 'employees/photos/pegawai.jpg']);

        $this->actingAs($admin)
            ->delete(route('admin.employees.destroy', $employee))
            ->assertRedirect(route('admin.employees.index'));

        Storage::disk('public')->assertMissing('employees/photos/pegawai.jpg');
        $this->assertModelMissing($employee);
        $this->assertDatabaseHas('audit_logs', ['action' => 'employee.deleted']);
    }

    public function test_news_editor_cannot_manage_employees(): void
    {
        Storage::fake('public');
        $editor = User::factory()->create();
        $admin = User::factory()->superAdmin()->create();
        $employee = Employee::factory()->for($admin, 'updatedBy')->create();

        $this->actingAs($editor)->get(route('admin.employees.index'))->assertForbidden();
        $this->actingAs($editor)->get(route('admin.employees.create'))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.employees.store'), $this->payload())->assertForbidden();
        $this->actingAs($editor)->get(route('admin.employees.edit', $employee))->assertForbidden();
        $this->actingAs($editor)->put(route('admin.employees.update', $employee), $this->payload())->assertForbidden();
        $this->actingAs($editor)->delete(route('admin.employees.destroy', $employee))->assertForbidden();
        $this->assertModelExists($employee);
    }

    public function test_employee_photo_rejects_file_larger_than_one_megabyte(): void
    {
        Storage::fake('public');
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->from(route('admin.employees.create'))
            ->post(route('admin.employees.store'), $this->payload([
                'photo' => UploadedFile::fake()->image('large.jpg', 600, 800)->size(1025),
            ]))
            ->assertRedirect(route('admin.employees.create'))
            ->assertSessionHasErrors(['photo' => 'Ukuran foto pegawai maksimal 1 MB.']);

        $this->assertDatabaseEmpty('employees');
    }

    public function test_employee_photo_rejects_non_image_file(): void
    {
        Storage::fake('public');
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->post(route('admin.employees.store'), $this->payload([
                'photo' => UploadedFile::fake()->create('script.php', 100, 'application/x-php'),
            ]))
            ->assertSessionHasErrors('photo');

        $this->assertDatabaseEmpty('employees');
    }

    public function test_public_employee_page_only_shows_published_profiles_and_photos(): void
    {
        Storage::fake('public');
        $admin = User::factory()->superAdmin()->create();
        $publishedEmployee = Employee::factory()->for($admin, 'updatedBy')->create([
            'name' => 'Pegawai Tampil',
            'photo_url' => 'employees/photos/tampil.jpg',
        ]);
        Employee::factory()->for($admin, 'updatedBy')->create([
            'name' => 'Pegawai Rahasia',
            'is_published' => false,
        ]);

        $this->get(route('employees'))
            ->assertSee('Pegawai Tampil')
            ->assertSee($publishedEmployee->photoUrl(), false)
            ->assertDontSee('Pegawai Rahasia');
    }

    /** @param array<string, mixed> $overrides @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return [...[
            'name' => 'Siti Rahma',
            'position' => 'Analis Kependudukan',
            'unit' => 'Bidang Pelayanan Pendaftaran Penduduk',
            'photo' => UploadedFile::fake()->image('pegawai.jpg', 600, 800)->size(900),
            'sort_order' => 2,
            'is_published' => '1',
        ], ...$overrides];
    }
}
