<?php

namespace Tests\Feature;

use App\Models\NewsArticle;
use App\Models\User;
use App\NewsStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class NewsArticleImageTest extends TestCase
{
    use RefreshDatabase;

    #[TestWith(['jpg'])]
    #[TestWith(['png'])]
    #[TestWith(['webp'])]
    public function test_editor_can_upload_a_cover_without_publishing(string $extension): void
    {
        Storage::fake('public');
        $editor = User::factory()->create();

        $this->actingAs($editor)->post(route('admin.news-articles.store'), $this->payload([
            'image' => UploadedFile::fake()->image('foto.'.$extension),
            'image_alt' => 'Petugas melayani masyarakat',
            'image_caption' => 'Pelayanan di kantor Dukcapil.',
        ]))->assertRedirect(route('admin.news-articles.index'))->assertSessionHasNoErrors();

        $article = NewsArticle::query()->sole();
        Storage::disk('public')->assertExists($article->image_path);
        $this->assertStringStartsWith('news/', $article->image_path);
        $this->assertNotSame('news/foto.'.$extension, $article->image_path);
        $this->assertSame('Petugas melayani masyarakat', $article->image_alt);
        $this->assertSame('Pelayanan di kantor Dukcapil.', $article->image_caption);
        $this->assertSame(NewsStatus::Draft, $article->status);
    }

    public function test_image_is_optional_and_unused_metadata_is_cleared(): void
    {
        $this->actingAs(User::factory()->create())->post(route('admin.news-articles.store'), $this->payload([
            'image_alt' => 'Tanpa gambar',
        ]))->assertSessionHasNoErrors();

        $article = NewsArticle::query()->sole();
        $this->assertNull($article->image_path);
        $this->assertNull($article->image_alt);
    }

    #[TestWith(['alt', 'image_alt'])]
    #[TestWith(['size', 'image'])]
    #[TestWith(['type', 'image'])]
    #[TestWith(['extension', 'image'])]
    #[TestWith(['svg', 'image'])]
    public function test_invalid_uploads_are_rejected_without_storing_files(string $case, string $field): void
    {
        Storage::fake('public');
        $image = match ($case) {
            'size' => UploadedFile::fake()->image('foto.jpg')->size(2049),
            'type' => UploadedFile::fake()->createWithContent('foto.jpg', '<?php echo "invalid";')->mimeType('text/plain'),
            'extension' => new UploadedFile(UploadedFile::fake()->image('foto.jpg')->getPathname(), 'foto.php', 'image/jpeg', null, true),
            'svg' => UploadedFile::fake()->createWithContent('foto.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'),
            default => UploadedFile::fake()->image('foto.jpg'),
        };

        $this->actingAs(User::factory()->create())->post(route('admin.news-articles.store'), $this->payload([
            'image' => $image,
            'image_alt' => $case === 'alt' ? '' : 'Foto pelayanan',
        ]))->assertSessionHasErrors($field);

        $this->assertDatabaseCount('news_articles', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_super_admin_can_replace_keep_and_remove_a_cover(): void
    {
        Storage::fake('public');
        $admin = User::factory()->superAdmin()->create();
        $article = NewsArticle::factory()->create(['image_path' => 'news/old.jpg', 'image_alt' => 'Foto lama']);
        Storage::disk('public')->put('news/old.jpg', 'old');

        $this->actingAs($admin)->put(route('admin.news-articles.update', $article), $this->payload([
            'image' => UploadedFile::fake()->image('baru.png'),
            'image_alt' => 'Foto baru',
            'image_caption' => 'Keterangan baru',
        ]))->assertSessionHasNoErrors();
        $article->refresh();
        $newPath = $article->image_path;
        Storage::disk('public')->assertMissing('news/old.jpg');
        Storage::disk('public')->assertExists($newPath);

        $this->put(route('admin.news-articles.update', $article), $this->payload([
            'image_alt' => 'Foto baru',
            'image_caption' => 'Keterangan diperbarui',
        ]))->assertSessionHasNoErrors();
        $this->assertSame($newPath, $article->refresh()->image_path);
        Storage::disk('public')->assertExists($newPath);

        $this->put(route('admin.news-articles.update', $article), $this->payload([
            'remove_image' => '1',
        ]))->assertSessionHasNoErrors();
        $article->refresh();
        $this->assertNull($article->image_path);
        $this->assertNull($article->image_alt);
        $this->assertNull($article->image_caption);
        Storage::disk('public')->assertMissing($newPath);
    }

    public function test_existing_cover_requires_alt_and_rejects_conflicting_removal(): void
    {
        Storage::fake('public');
        $editor = User::factory()->create();
        $article = NewsArticle::factory()->for($editor, 'author')->create(['image_path' => 'news/old.jpg', 'image_alt' => 'Foto lama']);
        Storage::disk('public')->put('news/old.jpg', 'old');

        $this->actingAs($editor)->put(route('admin.news-articles.update', $article), $this->payload())
            ->assertSessionHasErrors('image_alt');
        $this->put(route('admin.news-articles.update', $article), $this->payload([
            'image' => UploadedFile::fake()->image('baru.jpg'),
            'remove_image' => '1',
        ]))->assertSessionHasErrors('image');

        $this->assertSame('news/old.jpg', $article->refresh()->image_path);
        Storage::disk('public')->assertExists('news/old.jpg');
    }

    public function test_another_editor_cannot_replace_or_delete_the_cover(): void
    {
        Storage::fake('public');
        $article = NewsArticle::factory()->create(['image_path' => 'news/old.jpg', 'image_alt' => 'Foto lama']);
        Storage::disk('public')->put('news/old.jpg', 'old');
        $this->actingAs(User::factory()->create())->put(route('admin.news-articles.update', $article), $this->payload([
            'image' => UploadedFile::fake()->image('baru.jpg'),
            'image_alt' => 'Foto baru',
        ]))->assertForbidden();
        $this->delete(route('admin.news-articles.destroy', $article))->assertForbidden();
        $this->assertSame('news/old.jpg', $article->refresh()->image_path);
        $this->assertSame(['news/old.jpg'], Storage::disk('public')->allFiles());
    }

    public function test_deleting_an_article_removes_its_cover(): void
    {
        Storage::fake('public');
        $editor = User::factory()->create();
        $article = NewsArticle::factory()->for($editor, 'author')->create(['image_path' => 'news/old.jpg']);
        Storage::disk('public')->put('news/old.jpg', 'old');

        $this->actingAs($editor)->delete(route('admin.news-articles.destroy', $article))->assertRedirect();

        Storage::disk('public')->assertMissing('news/old.jpg');
        $this->assertModelMissing($article);
    }

    public function test_published_cover_appears_on_public_pages_and_social_metadata(): void
    {
        $article = NewsArticle::factory()->published()->create([
            'image_path' => 'news/cover.jpg',
            'image_alt' => 'Foto "pelayanan"',
            'image_caption' => '<script>caption</script>',
        ]);
        $imageUrl = Storage::disk('public')->url($article->image_path);
        foreach (['home', 'news.index'] as $routeName) {
            $this->get(route($routeName))->assertOk()->assertSee('src="'.$imageUrl.'"', false)
                ->assertSee('alt="Foto &quot;pelayanan&quot;"', false);
        }
        $this->get(route('news.show', $article))->assertOk()
            ->assertSee('src="'.$imageUrl.'"', false)
            ->assertSee('&lt;script&gt;caption&lt;/script&gt;', false)
            ->assertSee('<meta property="og:image" content="'.rtrim(config('dukcapil.public_url'), '/').$imageUrl.'">', false)
            ->assertSee('<meta name="twitter:image:alt" content="Foto &quot;pelayanan&quot;">', false);
    }

    public function test_article_without_cover_uses_default_social_image(): void
    {
        $article = NewsArticle::factory()->published()->create();
        $this->get(route('news.show', $article))->assertOk()
            ->assertSee('<meta property="og:image" content="'.config('dukcapil.seo.social_image').'">', false)
            ->assertDontSee('<figure', false);
    }

    public function test_guest_cannot_upload_a_cover(): void
    {
        Storage::fake('public');
        $this->post(route('admin.news-articles.store'), $this->payload([
            'image' => UploadedFile::fake()->image('foto.jpg'),
            'image_alt' => 'Foto pelayanan',
        ]))->assertRedirect(route('login'));
        $this->assertDatabaseCount('news_articles', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_admin_forms_support_uploads_and_show_the_existing_cover(): void
    {
        $editor = User::factory()->create();
        $article = NewsArticle::factory()->for($editor, 'author')->create([
            'image_path' => 'news/existing.jpg',
            'image_alt' => 'Foto pelayanan',
        ]);
        $this->actingAs($editor)->get(route('admin.news-articles.create'))->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="image"', false);
        $this->get(route('admin.news-articles.edit', $article))->assertOk()
            ->assertSee('src="'.Storage::disk('public')->url($article->image_path).'"', false)
            ->assertSee('name="remove_image"', false)
            ->assertSee('value="Foto pelayanan"', false);
    }

    public function test_invalid_replacement_preserves_the_existing_image_and_article(): void
    {
        Storage::fake('public');
        $editor = User::factory()->create();
        $article = NewsArticle::factory()->for($editor, 'author')->create([
            'image_path' => 'news/existing.jpg',
            'image_alt' => 'Foto lama',
        ]);
        Storage::disk('public')->put('news/existing.jpg', 'old');
        $title = $article->title;
        $this->actingAs($editor)->put(route('admin.news-articles.update', $article), $this->payload([
            'image' => UploadedFile::fake()->image('besar.jpg')->size(2049),
            'image_alt' => 'Foto baru',
        ]))->assertSessionHasErrors(['image' => 'Ukuran gambar maksimal 2 MB.']);
        $article->refresh();
        $this->assertSame($title, $article->title);
        $this->assertSame('Foto lama', $article->image_alt);
        $this->assertSame(['news/existing.jpg'], Storage::disk('public')->allFiles());
    }

    /** @param array<string, mixed> $overrides @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return [...[
            'title' => 'Berita dengan gambar',
            'slug' => 'berita-dengan-gambar',
            'excerpt' => 'Ringkasan berita pelayanan.',
            'body' => 'Isi berita pelayanan Dukcapil.',
            'status' => 'draft',
        ], ...$overrides];
    }
}
