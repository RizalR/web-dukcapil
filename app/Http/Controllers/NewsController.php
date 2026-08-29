<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use Illuminate\Contracts\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        return view('public.news.index', [
            'articles' => NewsArticle::query()->published()->with('author')->latest('published_at')->paginate(9),
        ]);
    }

    public function show(NewsArticle $newsArticle): View
    {
        abort_unless($newsArticle->status->value === 'published' && $newsArticle->published_at?->isPast(), 404);

        return view('public.news.show', ['article' => $newsArticle->load('author')]);
    }
}
