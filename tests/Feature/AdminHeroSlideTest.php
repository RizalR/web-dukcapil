<?php

namespace Tests\Feature;

use App\Models\HeroSlide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminHeroSlideTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_slide_forms(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $slide = HeroSlide::factory()->for($admin, 'updatedBy')->create();

        $this->actingAs($admin)
            ->get(route('admin.hero-slides.create'))
            ->assertOk()
            ->assertSee('Maksimal 1 MB dan harus berukuran tepat 1000 × 1208 piksel.');
        $this->actingAs($admin)->get(route('admin.hero-slides.edit', $slide))->assertOk();
    }

    public function test_super_admin_can_create_a_slide(): void
    {
        Storage::fake('public');
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->post(route('admin.hero-slides.store'), $this->payload())
            ->assertRedirect(route('admin.homepage.edit'))
            ->assertSessionHas('status');

        $slide = HeroSlide::query()->sole();
        Storage::disk('public')->assertExists($slide->image_path);
        $this->assertSame('Kegiatan Dukcapil', $slide->label);
        $this->assertTrue($slide->is_active);
        $this->assertDatabaseHas('audit_logs', ['action' => 'homepage.slide.created']);
    }

    public function test_news_editor_cannot_manage_slides(): void
    {
        Storage::fake('public');
        $editor = User::factory()->create();
        $admin = User::factory()->superAdmin()->create();
        $slide = HeroSlide::factory()->for($admin, 'updatedBy')->create();

        $this->actingAs($editor)->get(route('admin.hero-slides.create'))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.hero-slides.store'), $this->payload())->assertForbidden();
        $this->actingAs($editor)->get(route('admin.hero-slides.edit', $slide))->assertForbidden();
        $this->actingAs($editor)->put(route('admin.hero-slides.update', $slide), $this->payload())->assertForbidden();
        $this->actingAs($editor)->delete(route('admin.hero-slides.destroy', $slide))->assertForbidden();
        $this->assertModelExists($slide);
    }

    public function test_slide_rejects_image_with_incorrect_dimensions(): void
    {
        Storage::fake('public');
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->from(route('admin.hero-slides.create'))
            ->post(route('admin.hero-slides.store'), $this->payload([
                'image' => UploadedFile::fake()->image('incorrect-size.jpg', 1001, 1208),
            ]))
            ->assertRedirect(route('admin.hero-slides.create'))
            ->assertSessionHasErrors(['image' => 'Gambar slider harus berukuran tepat 1000 × 1208 piksel.']);

        $this->assertDatabaseEmpty('hero_slides');
    }

    public function test_slide_rejects_invalid_schedule(): void
    {
        Storage::fake('public');
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->from(route('admin.hero-slides.create'))
            ->post(route('admin.hero-slides.store'), $this->payload([
                'starts_at' => '2026-08-30 12:00:00',
                'ends_at' => '2026-08-29 12:00:00',
            ]))
            ->assertRedirect(route('admin.hero-slides.create'))
            ->assertSessionHasErrors(['ends_at']);

        $this->assertDatabaseEmpty('hero_slides');
    }

    public function test_slide_rejects_image_larger_than_one_megabyte(): void
    {
        Storage::fake('public');
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->from(route('admin.hero-slides.create'))
            ->post(route('admin.hero-slides.store'), $this->payload([
                'image' => UploadedFile::fake()->image('large.jpg', 1000, 1208)->size(1025),
            ]))
            ->assertRedirect(route('admin.hero-slides.create'))
            ->assertSessionHasErrors(['image' => 'Ukuran gambar slider maksimal 1 MB.']);

        $this->assertDatabaseEmpty('hero_slides');
    }

    public function test_super_admin_can_replace_a_slide_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('homepage/slides/old.jpg', 'image');
        $admin = User::factory()->superAdmin()->create();
        $slide = HeroSlide::factory()->for($admin, 'updatedBy')->create(['image_path' => 'homepage/slides/old.jpg']);

        $this->actingAs($admin)
            ->put(route('admin.hero-slides.update', $slide), $this->payload(['label' => 'Slide Diperbarui']))
            ->assertRedirect(route('admin.homepage.edit'));

        $slide->refresh();
        Storage::disk('public')->assertMissing('homepage/slides/old.jpg');
        Storage::disk('public')->assertExists($slide->image_path);
        $this->assertSame('Slide Diperbarui', $slide->label);
        $this->assertDatabaseHas('audit_logs', ['action' => 'homepage.slide.updated']);
    }

    public function test_super_admin_can_delete_a_slide_and_its_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('homepage/slides/old.jpg', 'image');
        $admin = User::factory()->superAdmin()->create();
        $slide = HeroSlide::factory()->for($admin, 'updatedBy')->create(['image_path' => 'homepage/slides/old.jpg']);

        $this->actingAs($admin)
            ->delete(route('admin.hero-slides.destroy', $slide))
            ->assertRedirect(route('admin.homepage.edit'));

        Storage::disk('public')->assertMissing('homepage/slides/old.jpg');
        $this->assertModelMissing($slide);
        $this->assertDatabaseHas('audit_logs', ['action' => 'homepage.slide.deleted']);
    }

    /** @param array<string, mixed> $overrides @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return [...[
            'label' => 'Kegiatan Dukcapil',
            'image' => UploadedFile::fake()->image('slide.jpg', 1000, 1208)->size(1000),
            'alt_text' => 'Pegawai Dukcapil mengikuti kegiatan bersama.',
            'caption' => 'Kegiatan pelayanan publik Dukcapil Kabupaten Gorontalo.',
            'sort_order' => 1,
            'is_active' => '1',
            'starts_at' => null,
            'ends_at' => null,
        ], ...$overrides];
    }
}
