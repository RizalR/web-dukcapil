<?php

namespace App\Http\Requests\Admin;

use App\Models\PopulationStatistic;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmPopulationStatisticImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', PopulationStatistic::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'alpha_num:ascii', 'size:40'],
            'publish_now' => ['sometimes', 'boolean'],
        ];
    }
}
