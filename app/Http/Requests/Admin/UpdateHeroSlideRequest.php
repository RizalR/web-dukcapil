<?php

namespace App\Http\Requests\Admin;

use App\Models\HeroSlide;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHeroSlideRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $heroSlide = $this->route('hero_slide');

        return $heroSlide instanceof HeroSlide && ($this->user()?->can('update', $heroSlide) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:160'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'extensions:jpg,jpeg,png,webp',
                'max:'.config('dukcapil.uploads.image_max_kilobytes'),
                'dimensions:width=1000,height=1208',
            ],
            'alt_text' => ['required', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:320'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['sometimes', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'image.dimensions' => 'Gambar slider harus berukuran tepat 1000 × 1208 piksel.',
            'image.max' => 'Ukuran gambar slider maksimal 1 MB.',
            'ends_at.after_or_equal' => 'Waktu selesai harus setelah atau sama dengan waktu mulai.',
        ];
    }
}
