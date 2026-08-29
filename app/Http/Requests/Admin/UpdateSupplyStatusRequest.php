<?php

namespace App\Http\Requests\Admin;

use App\Models\SupplyStatus;
use App\SupplyLevel;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplyStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $supply = $this->route('supply_status');

        return $supply instanceof SupplyStatus && ($this->user()?->can('update', $supply) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(SupplyLevel::class)],
            'note' => ['nullable', 'string', 'max:500'],
            'source' => ['required', 'string', 'max:180'],
            'checked_at' => ['required', 'date', 'before_or_equal:now'],
        ];
    }
}
