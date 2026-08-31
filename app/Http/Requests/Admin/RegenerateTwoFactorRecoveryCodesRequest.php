<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegenerateTwoFactorRecoveryCodesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() === true
            && $this->user()->hasConfirmedTwoFactorAuthentication();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'current_password:web'],
            'code' => ['required', 'string', 'regex:/^\d{6}$/'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'code.regex' => 'Kode authenticator harus terdiri dari 6 digit.',
        ];
    }
}
