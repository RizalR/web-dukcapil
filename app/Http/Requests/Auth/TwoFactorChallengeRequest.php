<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TwoFactorChallengeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['nullable', 'string', 'regex:/^\d{6}$/', 'required_without:recovery_code', 'prohibits:recovery_code'],
            'recovery_code' => ['nullable', 'string', 'max:100', 'required_without:code', 'prohibits:code'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'code.regex' => 'Kode authenticator harus terdiri dari 6 digit.',
            'code.required_without' => 'Masukkan kode authenticator atau recovery code.',
            'code.prohibits' => 'Gunakan salah satu: kode authenticator atau recovery code.',
            'recovery_code.required_without' => 'Masukkan kode authenticator atau recovery code.',
            'recovery_code.prohibits' => 'Gunakan salah satu: kode authenticator atau recovery code.',
        ];
    }
}
