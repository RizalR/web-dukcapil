<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $siteUrl = rtrim((string) config('dukcapil.public_url'), '/');
        $staticUrls = collect([
            'home',
            'services',
            'whistleblowing.index',
            'profile',
            'news.index',
            'information',
            'thematic-statistics',
            'documents',
            'employees',
            'employee-apps',
            'contact',
        ])->map(fn (string $routeName): array => [
            'loc' => $siteUrl.route($routeName, absolute: false),
            'lastmod' => null,
        ]);
        $articleUrls = NewsArticle::query()
            ->published()
            ->latest('published_at')
            ->get()
            ->map(fn (NewsArticle $article): array => [
                'loc' => $siteUrl.route('news.show', $article, false),
                'lastmod' => $article->updated_at->toDateString(),
            ]);

        return response()
            ->view('sitemap', ['urls' => $staticUrls->concat($articleUrls)])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
