<?php

namespace App\Http\Requests\Admin;

use App\Models\PopulationStatistic;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePopulationStatisticRequest extends FormRequest
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
            'label' => ['required', 'string', 'max:120'],
            'category' => ['required', 'string', 'max:80'],
            'value' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:30'],
            'period' => ['required', 'string', 'max:80'],
            'area' => ['required', 'string', 'max:120'],
            'source' => ['required', 'string', 'max:180'],
            'source_url' => ['nullable', 'url:http,https', 'max:2048'],
            'published_at' => ['nullable', 'date'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['label', 'category', 'period', 'area'])) {
                return;
            }

            $exists = PopulationStatistic::query()
                ->where('label', $this->string('label')->toString())
                ->where('category', $this->string('category')->toString())
                ->where('period', $this->string('period')->toString())
                ->where('area', $this->string('area')->toString())
                ->exists();

            if ($exists) {
                $validator->errors()->add('label', 'Indikator untuk wilayah dan periode tersebut sudah ada.');
            }
        }];
    }
}
