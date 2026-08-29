<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\HeroSlide;
use App\Models\HomepageSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminHomepageAppearanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_homepage_management(): void
    {
        $admin = User::factory()->superAdmin()->create();
        HeroSlide::factory()->for($admin, 'updatedBy')->create(['label' => 'Dokumentasi Pelayanan']);

        $this->actingAs($admin)
            ->get(route('admin.homepage.edit'))
            ->assertOk()
            ->assertSee('Background dan slider')
            ->assertSee('Dokumentasi Pelayanan');
    }

    public function test_super_admin_can_upload_initial_homepage_background(): void
    {
        Storage::fake('public');
        $admin = User::factory()->superAdmin()->create();
        $image = UploadedFile::fake()->image('background.jpg', 1600, 700)->size(1000);

        $this->actingAs($admin)
            ->put(route('admin.homepage.update'), ['background_image' => $image])
            ->assertRedirect(route('admin.homepage.edit'));

        $homepageSetting = HomepageSetting::query()->sole();
        Storage::disk('public')->assertExists($homepageSetting->background_image_path);
    }

    public function test_super_admin_can_replace_homepage_background_and_remove_old_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('homepage/backgrounds/old.jpg', 'image');
        $admin = User::factory()->superAdmin()->create();
        HomepageSetting::factory()->create([
            'background_image_path' => 'homepage/backgrounds/old.jpg',
            'updated_by' => $admin,
        ]);
        $image = UploadedFile::fake()->image('background.jpg', 1600, 700)->size(1000);

        $this->actingAs($admin)
            ->put(route('admin.homepage.update'), ['background_image' => $image])
            ->assertRedirect(route('admin.homepage.edit'))
            ->assertSessionHas('status');

        $homepageSetting = HomepageSetting::query()->sole();
        Storage::disk('public')->assertMissing('homepage/backgrounds/old.jpg');
        Storage::disk('public')->assertExists($homepageSetting->background_image_path);
        $this->assertSame($admin->id, $homepageSetting->updated_by);
        $this->assertSame(1, AuditLog::query()->where('action', 'homepage.background.updated')->count());
    }

    public function test_super_admin_can_reset_homepage_background_and_remove_uploaded_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('homepage/backgrounds/old.jpg', 'image');
        $admin = User::factory()->superAdmin()->create();
        HomepageSetting::factory()->create([
            'background_image_path' => 'homepage/backgrounds/old.jpg',
            'updated_by' => $admin,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.homepage.background.destroy'))
            ->assertRedirect(route('admin.homepage.edit'));

        Storage::disk('public')->assertMissing('homepage/backgrounds/old.jpg');
        $this->assertNull(HomepageSetting::query()->sole()->background_image_path);
        $this->assertDatabaseHas('audit_logs', ['action' => 'homepage.background.reset']);
    }

    public function test_news_editor_cannot_manage_homepage_background(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)->get(route('admin.homepage.edit'))->assertForbidden();
        $this->actingAs($editor)->put(route('admin.homepage.update'))->assertForbidden();
        $this->actingAs($editor)->delete(route('admin.homepage.background.destroy'))->assertForbidden();
    }

    public function test_homepage_background_rejects_an_image_below_minimum_dimensions(): void
    {
        Storage::fake('public');
        $admin = User::factory()->superAdmin()->create();
        $image = UploadedFile::fake()->image('small.jpg', 800, 400);

        $this->actingAs($admin)
            ->from(route('admin.homepage.edit'))
            ->put(route('admin.homepage.update'), ['background_image' => $image])
            ->assertRedirect(route('admin.homepage.edit'))
            ->assertSessionHasErrors('background_image');

        $this->assertDatabaseEmpty('homepage_settings');
    }

    public function test_homepage_background_rejects_a_non_image_upload(): void
    {
        Storage::fake('public');
        $admin = User::factory()->superAdmin()->create();
        $file = UploadedFile::fake()->create('script.php', 100, 'application/x-php');

        $this->actingAs($admin)
            ->from(route('admin.homepage.edit'))
            ->put(route('admin.homepage.update'), ['background_image' => $file])
            ->assertRedirect(route('admin.homepage.edit'))
            ->assertSessionHasErrors('background_image');

        $this->assertDatabaseEmpty('homepage_settings');
    }

    public function test_homepage_background_rejects_image_larger_than_one_megabyte(): void
    {
        Storage::fake('public');
        $admin = User::factory()->superAdmin()->create();
        $image = UploadedFile::fake()->image('large.jpg', 1600, 700)->size(1025);

        $this->actingAs($admin)
            ->from(route('admin.homepage.edit'))
            ->put(route('admin.homepage.update'), ['background_image' => $image])
            ->assertRedirect(route('admin.homepage.edit'))
            ->assertSessionHasErrors(['background_image' => 'Ukuran gambar background maksimal 1 MB.']);

        $this->assertDatabaseEmpty('homepage_settings');
    }
}
