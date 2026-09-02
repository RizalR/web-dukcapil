<?php

namespace App\Http\Controllers;

use App\Models\WhistleblowingPublication;
use App\Models\WhistleblowingSyncState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class WhistleblowingPublicationController extends Controller
{
    public function __invoke(Request $request): View
    {
        $categories = WhistleblowingPublication::query()
            ->visible()
            ->orderBy('category_label')
            ->pluck('category_label', 'category_code');
        $years = WhistleblowingPublication::query()
            ->visible()
            ->get(['published_at'])
            ->map(fn (WhistleblowingPublication $publication): int => $publication->published_at->year)
            ->unique()
            ->sortDesc()
            ->values();

        $requestedCategory = $request->string('kategori')->toString();
        $selectedCategory = $categories->has($requestedCategory) ? $requestedCategory : '';
        $requestedYear = $request->integer('tahun');
        $selectedYear = $years->contains($requestedYear) ? $requestedYear : null;

        $publications = WhistleblowingPublication::query()
            ->visible()
            ->when($selectedCategory !== '', fn ($query) => $query->where('category_code', $selectedCategory))
            ->when($selectedYear !== null, fn ($query) => $query->whereYear('published_at', $selectedYear))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();
        $syncState = WhistleblowingSyncState::query()->where('source', 'siprima')->first();
        $staleAfterMinutes = max(10, (int) config('services.siprima_whistleblowing.stale_after_minutes', 30));
        $isStale = $syncState?->last_succeeded_at === null
            || $syncState->last_succeeded_at->lt(now()->subMinutes($staleAfterMinutes));

        return view('public.whistleblowing', [
            'publications' => $publications,
            'categories' => $categories,
            'years' => $years,
            'selectedCategory' => $selectedCategory,
            'selectedYear' => $selectedYear,
            'syncState' => $syncState,
            'isStale' => $isStale,
        ]);
    }
}
