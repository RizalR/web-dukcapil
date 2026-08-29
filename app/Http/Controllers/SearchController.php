<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\NewsArticle;
use App\Models\PublicDocument;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $validated = $request->validate(['q' => ['required', 'string', 'min:2', 'max:100']]);
        $term = str($validated['q'])->squish()->toString();

        return view('public.search', [
            'term' => $term,
            'articles' => NewsArticle::query()->published()->where(fn ($query) => $query->where('title', 'like', "%{$term}%")->orWhere('excerpt', 'like', "%{$term}%"))->latest('published_at')->limit(10)->get(),
            'documents' => PublicDocument::query()->published()->where(fn ($query) => $query->where('title', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%"))->limit(10)->get(),
            'employees' => Employee::query()->where('is_published', true)->where(fn ($query) => $query->where('name', 'like', "%{$term}%")->orWhere('position', 'like', "%{$term}%"))->limit(10)->get(),
        ]);
    }
}
