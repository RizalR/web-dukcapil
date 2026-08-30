<?php

namespace App\Http\Requests\Admin;

use App\LeadershipRole;
use App\Models\LeadershipProfile;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadershipProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', LeadershipProfile::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => ['required', Rule::enum(LeadershipRole::class), Rule::unique('leadership_profiles', 'role')],
            'name' => ['required', 'string', 'max:160'],
            'photo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'extensions:jpg,jpeg,png,webp',
                'max:'.config('dukcapil.uploads.image_max_kilobytes'),
                'dimensions:ratio=4/5',
            ],
            'is_published' => ['required', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'role.unique' => 'Jabatan pimpinan tersebut sudah terdaftar.',
            'photo.dimensions' => 'Foto pimpinan harus menggunakan rasio 4:5.',
            'photo.max' => 'Ukuran foto pimpinan maksimal 1 MB.',
        ];
    }
}
