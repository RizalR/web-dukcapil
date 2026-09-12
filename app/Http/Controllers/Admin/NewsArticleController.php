<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNewsArticleRequest;
use App\Http\Requests\Admin\UpdateNewsArticleRequest;
use App\Models\NewsArticle;
use App\NewsStatus;
use App\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class NewsArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('viewAny', NewsArticle::class);

        return view('admin.news.index', ['articles' => NewsArticle::query()->with('author')->latest()->paginate(15)]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', NewsArticle::class);

        return view('admin.news.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNewsArticleRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = Arr::except($request->validated(), ['image']);
        $data['author_id'] = $request->user()->id;
        $data['is_featured'] = $request->boolean('is_featured');
        $data = $this->enforcePublishingRules($data, $request->user()->isSuperAdmin());
        $imagePath = $request->hasFile('image') ? $request->file('image')->store('news', 'public') : null;

        if ($imagePath === false) {
            throw new RuntimeException('Gambar berita tidak dapat disimpan.');
        }

        $data['image_path'] = $imagePath;

        if ($imagePath === null) {
            $data['image_alt'] = null;
            $data['image_caption'] = null;
        }

        try {
            $article = NewsArticle::query()->create($data);
        } catch (Throwable $exception) {
            if ($imagePath !== null) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $exception;
        }
        $auditLogger->record('news.created', "Berita {$article->title} dibuat.", $article, ['status' => $article->status->value]);

        return redirect()->route('admin.news-articles.index')->with('status', 'Berita berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(NewsArticle $newsArticle): RedirectResponse
    {
        return redirect()->route('admin.news-articles.edit', $newsArticle);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(NewsArticle $newsArticle): View
    {
        $this->authorize('update', $newsArticle);

        return view('admin.news.edit', ['article' => $newsArticle]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNewsArticleRequest $request, NewsArticle $newsArticle, AuditLogger $auditLogger): RedirectResponse
    {
        $data = Arr::except($request->validated(), ['image', 'remove_image']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data = $this->enforcePublishingRules($data, $request->user()->isSuperAdmin());
        $oldImagePath = $newsArticle->image_path;
        $newImagePath = $request->hasFile('image') ? $request->file('image')->store('news', 'public') : null;

        if ($newImagePath === false) {
            throw new RuntimeException('Gambar berita tidak dapat disimpan.');
        }

        $data['image_path'] = $request->boolean('remove_image') ? null : ($newImagePath ?? $oldImagePath);

        if ($data['image_path'] === null) {
            $data['image_alt'] = null;
            $data['image_caption'] = null;
        }

        try {
            $newsArticle->update($data);
        } catch (Throwable $exception) {
            if ($newImagePath !== null) {
                Storage::disk('public')->delete($newImagePath);
            }

            throw $exception;
        }

        if ($oldImagePath !== null && $oldImagePath !== $data['image_path']) {
            Storage::disk('public')->delete($oldImagePath);
        }
        $auditLogger->record('news.updated', "Berita {$newsArticle->title} diperbarui.", $newsArticle, ['status' => $newsArticle->status->value]);

        return redirect()->route('admin.news-articles.index')->with('status', 'Berita berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NewsArticle $newsArticle, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('delete', $newsArticle);
        $title = $newsArticle->title;
        $auditLogger->record('news.deleted', "Berita {$title} dihapus.", $newsArticle);
        $imagePath = $newsArticle->image_path;
        $newsArticle->delete();

        if ($imagePath !== null) {
            Storage::disk('public')->delete($imagePath);
        }

        return redirect()->route('admin.news-articles.index')->with('status', 'Berita berhasil dihapus.');
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private function enforcePublishingRules(array $data, bool $canPublish): array
    {
        if (! $canPublish || $data['status'] !== NewsStatus::Published->value) {
            $data['status'] = NewsStatus::Draft;
            $data['published_at'] = null;
            $data['is_featured'] = false;

            return $data;
        }

        $data['published_at'] = now();

        return $data;
    }
}
