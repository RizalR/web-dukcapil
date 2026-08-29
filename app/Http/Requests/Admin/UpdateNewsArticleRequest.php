<?php

namespace App\Http\Requests\Admin;

use App\Models\NewsArticle;
use App\NewsStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $article = $this->route('news_article');

        return $article instanceof NewsArticle && ($this->user()?->can('update', $article) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'string', 'max:200', 'alpha_dash:ascii', Rule::unique('news_articles', 'slug')->ignore($this->route('news_article'))],
            'excerpt' => ['required', 'string', 'max:320'],
            'body' => ['required', 'string', 'max:50000'],
            'status' => ['required', Rule::enum(NewsStatus::class)],
            'is_featured' => ['sometimes', 'boolean'],
            'source_url' => ['nullable', 'url:http,https', 'max:2048'],
        ];
    }
}
