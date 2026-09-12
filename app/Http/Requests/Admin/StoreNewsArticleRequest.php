<?php

namespace App\Http\Requests\Admin;

use App\Models\NewsArticle;
use App\NewsStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNewsArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', NewsArticle::class) ?? false;
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
            'slug' => ['required', 'string', 'max:200', 'alpha_dash:ascii', 'unique:news_articles,slug'],
            'excerpt' => ['required', 'string', 'max:320'],
            'body' => ['required', 'string', 'max:50000'],
            'status' => ['required', Rule::enum(NewsStatus::class)],
            'is_featured' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:2048'],
            'image_alt' => ['nullable', Rule::requiredIf($this->hasFile('image')), 'string', 'max:255'],
            'image_caption' => ['nullable', 'string', 'max:320'],
            'source_url' => ['nullable', 'url:http,https', 'max:2048'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'image.image' => 'Berkas harus berupa gambar JPG, PNG, atau WebP.',
            'image.mimes' => 'Format gambar harus JPG, PNG, atau WebP.',
            'image.extensions' => 'Ekstensi gambar harus JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran gambar maksimal 2 MB.',
            'image.prohibited_if' => 'Pilih ganti gambar atau hapus gambar, bukan keduanya.',
            'image_alt.required' => 'Teks alternatif wajib diisi jika berita memiliki gambar.',
        ];
    }
}
