<?php

namespace App\Http\Requests\Admin;

use App\Models\PrintingStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePrintingStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $printingStatus = $this->route('printingStatus');

        return $printingStatus instanceof PrintingStatus && ($this->user()?->can('update', $printingStatus) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'is_available' => ['required', 'boolean'],
            'note' => ['nullable', 'string', 'max:500'],
            'source' => ['required', 'string', 'max:180'],
            'checked_at' => ['required', 'date', 'before_or_equal:now'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'is_available.required' => 'Pilih status Ada atau Tidak ada.',
            'is_available.boolean' => 'Status pencetakan harus berupa Ada atau Tidak ada.',
            'source.required' => 'Sumber pembaruan wajib diisi.',
            'checked_at.required' => 'Waktu pemeriksaan wajib diisi.',
            'checked_at.before_or_equal' => 'Waktu pemeriksaan tidak boleh melewati waktu sekarang.',
        ];
    }
}
