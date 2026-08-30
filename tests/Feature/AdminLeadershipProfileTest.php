<?php

namespace Tests\Feature;

use App\LeadershipRole;
use App\Models\LeadershipProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminLeadershipProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_from_leadership_profile_admin(): void
    {
        $this->get(route('admin.leadership-profiles.index'))
            ->assertRedirect(route('login'));
    }

    public function test_super_admin_can_open_leadership_profile_management_and_forms(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $profile = LeadershipProfile::factory()->regent()->for($admin, 'updatedBy')->create([
            'name' => 'Pimpinan Kabupaten',
        ]);

        $this->actingAs($admin)->get(route('admin.leadership-profiles.index'))->assertSee('Pimpinan Kabupaten');
        $this->actingAs($admin)->get(route('admin.leadership-profiles.create'))->assertSee('Tambah profil pimpinan');
        $this->actingAs($admin)->get(route('admin.leadership-profiles.edit', $profile))->assertSee('Edit profil pimpinan');
    }

    public function test_super_admin_can_create_leadership_profile_with_photo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->post(route('admin.leadership-profiles.store'), $this->payload())
            ->assertRedirect(route('admin.leadership-profiles.index'))
            ->assertSessionHas('status');

        $profile = LeadershipProfile::query()->sole();
        Storage::disk('public')->assertExists($profile->photo_path);
        $this->assertSame('H. Sofyan Puhi, S.T.', $profile->name);
        $this->assertSame(LeadershipRole::Regent, $profile->role);
        $this->assertSame(LeadershipRole::Regent->sortOrder(), $profile->sort_order);
        $this->assertSame($admin->id, $profile->updated_by);
        $this->assertTrue($profile->is_published);
        $this->assertDatabaseHas('audit_logs', ['action' => 'leadership_profile.created', 'user_id' => $admin->id]);
    }

    public function test_super_admin_can_update_profile_and_replace_stored_photo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('leadership/photos/old.jpg', 'image');
        $admin = User::factory()->superAdmin()->create();
        $profile = LeadershipProfile::factory()->regent()->for($admin, 'updatedBy')->create([
            'photo_path' => 'leadership/photos/old.jpg',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.leadership-profiles.update', $profile), $this->payload([
                'name' => 'Nama Pimpinan Diperbarui',
            ]))
            ->assertRedirect(route('admin.leadership-profiles.index'));

        $profile->refresh();
        Storage::disk('public')->assertMissing('leadership/photos/old.jpg');
        Storage::disk('public')->assertExists($profile->photo_path);
        $this->assertSame('Nama Pimpinan Diperbarui', $profile->name);
        $this->assertDatabaseHas('audit_logs', ['action' => 'leadership_profile.updated']);
    }

    public function test_super_admin_can_delete_profile_and_stored_photo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('leadership/photos/profile.jpg', 'image');
        $admin = User::factory()->superAdmin()->create();
        $profile = LeadershipProfile::factory()->regent()->for($admin, 'updatedBy')->create([
            'photo_path' => 'leadership/photos/profile.jpg',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.leadership-profiles.destroy', $profile))
            ->assertRedirect(route('admin.leadership-profiles.index'));

        Storage::disk('public')->assertMissing('leadership/photos/profile.jpg');
        $this->assertModelMissing($profile);
        $this->assertDatabaseHas('audit_logs', ['action' => 'leadership_profile.deleted']);
    }

    public function test_news_editor_cannot_manage_leadership_profiles(): void
    {
        Storage::fake('public');
        $editor = User::factory()->create();
        $admin = User::factory()->superAdmin()->create();
        $profile = LeadershipProfile::factory()->regent()->for($admin, 'updatedBy')->create();

        $this->actingAs($editor)->get(route('admin.leadership-profiles.index'))->assertForbidden();
        $this->actingAs($editor)->get(route('admin.leadership-profiles.create'))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.leadership-profiles.store'), $this->payload())->assertForbidden();
        $this->actingAs($editor)->get(route('admin.leadership-profiles.edit', $profile))->assertForbidden();
        $this->actingAs($editor)->put(route('admin.leadership-profiles.update', $profile), $this->payload())->assertForbidden();
        $this->actingAs($editor)->delete(route('admin.leadership-profiles.destroy', $profile))->assertForbidden();
        $this->assertModelExists($profile);
    }

    public function test_duplicate_leadership_role_is_rejected(): void
    {
        Storage::fake('public');
        $admin = User::factory()->superAdmin()->create();
        LeadershipProfile::factory()->regent()->for($admin, 'updatedBy')->create();

        $this->actingAs($admin)
            ->from(route('admin.leadership-profiles.create'))
            ->post(route('admin.leadership-profiles.store'), $this->payload())
            ->assertRedirect(route('admin.leadership-profiles.create'))
            ->assertSessionHasErrors(['role' => 'Jabatan pimpinan tersebut sudah terdaftar.']);

        $this->assertSame(1, LeadershipProfile::query()->count());
    }

    public function test_leadership_photo_rejects_wrong_aspect_ratio(): void
    {
        Storage::fake('public');
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->from(route('admin.leadership-profiles.create'))
            ->post(route('admin.leadership-profiles.store'), $this->payload([
                'photo' => UploadedFile::fake()->image('square.jpg', 800, 800)->size(900),
            ]))
            ->assertRedirect(route('admin.leadership-profiles.create'))
            ->assertSessionHasErrors(['photo' => 'Foto pimpinan harus menggunakan rasio 4:5.']);

        $this->assertDatabaseEmpty('leadership_profiles');
    }

    public function test_leadership_photo_rejects_file_larger_than_one_megabyte(): void
    {
        Storage::fake('public');
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->from(route('admin.leadership-profiles.create'))
            ->post(route('admin.leadership-profiles.store'), $this->payload([
                'photo' => UploadedFile::fake()->image('large.jpg', 800, 1000)->size(1025),
            ]))
            ->assertRedirect(route('admin.leadership-profiles.create'))
            ->assertSessionHasErrors(['photo' => 'Ukuran foto pimpinan maksimal 1 MB.']);

        $this->assertDatabaseEmpty('leadership_profiles');
    }

    public function test_homepage_and_profile_page_show_only_published_leaders_in_official_order(): void
    {
        $admin = User::factory()->superAdmin()->create();
        LeadershipProfile::factory()->departmentHead()->for($admin, 'updatedBy')->create([
            'name' => 'Kepala Dinas Tampil',
        ]);
        LeadershipProfile::factory()->regent()->for($admin, 'updatedBy')->create([
            'name' => 'Bupati Tampil',
        ]);
        LeadershipProfile::factory()->viceRegent()->for($admin, 'updatedBy')->create([
            'name' => 'Wakil Bupati Disembunyikan',
            'is_published' => false,
        ]);

        $this->get(route('home'))
            ->assertSeeInOrder(['Bupati Tampil', 'Kepala Dinas Tampil'])
            ->assertDontSee('Wakil Bupati Disembunyikan');

        $this->get(route('profile'))
            ->assertSeeInOrder(['Bupati Tampil', 'Kepala Dinas Tampil'])
            ->assertDontSee('Wakil Bupati Disembunyikan');
    }

    public function test_public_leadership_name_is_html_escaped(): void
    {
        $admin = User::factory()->superAdmin()->create();
        LeadershipProfile::factory()->regent()->for($admin, 'updatedBy')->create([
            'name' => '<script>alert("xss")</script>',
        ]);

        $this->get(route('home'))
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert("xss")</script>', false);
    }

    /** @param array<string, mixed> $overrides @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return [...[
            'role' => LeadershipRole::Regent->value,
            'name' => 'H. Sofyan Puhi, S.T.',
            'photo' => UploadedFile::fake()->image('pimpinan.jpg', 800, 1000)->size(900),
            'is_published' => '1',
        ], ...$overrides];
    }
}
