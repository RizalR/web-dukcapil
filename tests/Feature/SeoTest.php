<?php

namespace Tests\Feature;

use App\Models\NewsArticle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_displays_production_canonical_and_social_metadata(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://dukcapil.gorontalokab.go.id/">', false)
            ->assertSee('<meta name="robots" content="index,follow,max-image-preview:large">', false)
            ->assertSee('<meta property="og:locale" content="id_ID">', false)
            ->assertSee('<meta property="og:url" content="https://dukcapil.gorontalokab.go.id/">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee('type="application/ld+json"', false)
            ->assertSee('"@type":"GovernmentOrganization"', false);
    }

    public function test_search_results_are_not_indexed(): void
    {
        $this->get(route('search', ['q' => 'dukcapil']))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex,follow">', false)
            ->assertSee('<link rel="canonical" href="https://dukcapil.gorontalokab.go.id/pencarian">', false);
    }

    public function test_published_news_has_article_metadata(): void
    {
        $article = NewsArticle::factory()->published()->create([
            'title' => 'Pelayanan Dukcapil Terbaru',
            'slug' => 'pelayanan-dukcapil-terbaru',
            'excerpt' => 'Informasi pelayanan terbaru untuk masyarakat Kabupaten Gorontalo.',
            'published_at' => '2026-08-29 08:00:00',
        ]);

        $this->get(route('news.show', $article))
            ->assertOk()
            ->assertSee('<meta property="og:type" content="article">', false)
            ->assertSee('<meta property="article:published_time" content="2026-08-29T08:00:00+08:00">', false)
            ->assertSee('<meta property="og:description" content="Informasi pelayanan terbaru untuk masyarakat Kabupaten Gorontalo.">', false)
            ->assertSee('<link rel="canonical" href="https://dukcapil.gorontalokab.go.id/berita/pelayanan-dukcapil-terbaru">', false);
    }

    public function test_sitemap_contains_public_pages_and_only_published_news(): void
    {
        $published = NewsArticle::factory()->published()->create(['slug' => 'berita-terbit-seo']);
        NewsArticle::factory()->create(['slug' => 'berita-draf-seo']);

        $response = $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('https://dukcapil.gorontalokab.go.id/', false)
            ->assertSee('https://dukcapil.gorontalokab.go.id/informasi', false)
            ->assertSee('https://dukcapil.gorontalokab.go.id/berita/'.$published->slug, false)
            ->assertDontSee('berita-draf-seo', false)
            ->assertDontSee('/admin', false)
            ->assertDontSee('/masuk', false)
            ->assertDontSee('/pencarian', false);

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', trim($response->getContent()));
    }

    public function test_robots_file_blocks_private_routes_and_references_sitemap(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        $this->assertIsString($robots);
        $this->assertStringContainsString('Disallow: /admin/', $robots);
        $this->assertStringContainsString('Disallow: /masuk', $robots);
        $this->assertStringContainsString('Disallow: /pencarian', $robots);
        $this->assertStringContainsString('Sitemap: https://dukcapil.gorontalokab.go.id/sitemap.xml', $robots);
    }

    public function test_apache_fallback_redirects_only_the_production_domain_to_https(): void
    {
        $htaccess = file_get_contents(public_path('.htaccess'));

        $this->assertIsString($htaccess);
        $this->assertStringContainsString('^dukcapil\.gorontalokab\.go\.id$', $htaccess);
        $this->assertStringContainsString('https://dukcapil.gorontalokab.go.id%{REQUEST_URI}', $htaccess);
        $this->assertStringContainsString('%{HTTP:X-Forwarded-Proto} !https', $htaccess);
    }
}
