<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\NewsArticle;
use App\Models\User;
use App\NewsStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNewsArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_news_index_with_draft_and_published_articles(): void
    {
        $admin = User::factory()->superAdmin()->create();
        NewsArticle::factory()->for($admin, 'author')->create(['title' => 'Berita Draf']);
        NewsArticle::factory()->for($admin, 'author')->published()->create(['title' => 'Berita Terbit']);

        $this->actingAs($admin)
            ->get(route('admin.news-articles.index'))
            ->assertOk()
            ->assertSee('Berita Draf')
            ->assertSee('Terbitkan')
            ->assertSee('Berita Terbit')
            ->assertSee('Tarik');
    }

    public function test_editor_can_create_news_but_cannot_publish_it(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)->post(route('admin.news-articles.store'), $this->payload(['status' => 'published', 'is_featured' => '1']))
            ->assertRedirect(route('admin.news-articles.index'));

        $article = NewsArticle::query()->sole();
        $this->assertSame(NewsStatus::Draft, $article->status);
        $this->assertNull($article->published_at);
        $this->assertFalse($article->is_featured);
        $this->assertDatabaseHas('audit_logs', ['action' => 'news.created', 'user_id' => $editor->id]);
    }

    public function test_super_admin_can_publish_news(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $article = NewsArticle::factory()->for($admin, 'author')->create();

        $this->actingAs($admin)->put(route('admin.news-articles.publish', $article))
            ->assertSessionHas('status');

        $this->assertSame(NewsStatus::Published, $article->fresh()->status);
        $this->assertNotNull($article->fresh()->published_at);
        $this->assertSame(1, AuditLog::query()->where('action', 'news.published')->count());
    }

    public function test_editor_cannot_edit_another_editors_news(): void
    {
        $editor = User::factory()->create();
        $article = NewsArticle::factory()->create();

        $this->actingAs($editor)->get(route('admin.news-articles.edit', $article))->assertForbidden();
    }

    /** @param array<string, mixed> $overrides @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return [...[
            'title' => 'Pembaruan Informasi Dukcapil',
            'slug' => 'pembaruan-informasi-dukcapil',
            'excerpt' => 'Ringkasan informasi kependudukan Kabupaten Gorontalo.',
            'body' => 'Isi berita yang lengkap dan informatif bagi masyarakat.',
            'status' => 'draft',
            'source_url' => 'https://dukcapil.gorontalokab.go.id/',
        ], ...$overrides];
    }
}
