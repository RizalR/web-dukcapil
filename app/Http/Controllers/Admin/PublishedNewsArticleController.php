<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use App\NewsStatus;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;

class PublishedNewsArticleController extends Controller
{
    public function update(NewsArticle $newsArticle, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('publish', $newsArticle);
        $newsArticle->update(['status' => NewsStatus::Published, 'published_at' => now()]);
        $auditLogger->record('news.published', "Berita {$newsArticle->title} diterbitkan.", $newsArticle);

        return back()->with('status', 'Berita berhasil diterbitkan.');
    }

    public function destroy(NewsArticle $newsArticle, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('publish', $newsArticle);
        $newsArticle->update(['status' => NewsStatus::Draft, 'published_at' => null, 'is_featured' => false]);
        $auditLogger->record('news.unpublished', "Berita {$newsArticle->title} ditarik menjadi draf.", $newsArticle);

        return back()->with('status', 'Berita dikembalikan menjadi draf.');
    }
}
