<?php

namespace App\Http\Requests\Admin;

use App\Models\PopulationStatistic;
use App\PopulationStatisticCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PreviewPopulationStatisticImportRequest extends FormRequest
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
        $rules = [
            'workbooks' => ['nullable', 'array:'.implode(',', $this->categorySlugs())],
            'period' => ['required', 'string', 'max:80'],
            'source' => ['required', 'string', 'max:180'],
            'source_url' => ['nullable', 'url:http,https', 'max:2048'],
        ];

        foreach ($this->categorySlugs() as $slug) {
            $rules["workbooks.{$slug}"] = [
                'nullable',
                'file',
                'extensions:xlsx',
                'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip,application/octet-stream',
                'max:2048',
            ];
        }

        return $rules;
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $hasWorkbook = collect($this->categorySlugs())
                    ->contains(fn (string $slug): bool => $this->hasFile("workbooks.{$slug}"));

                if (! $hasWorkbook) {
                    $validator->errors()->add('workbooks', 'Pilih sedikitnya satu file Excel agregat.');
                }
            },
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        $attributes = [
            'workbooks' => 'file Excel agregat',
            'period' => 'periode',
            'source' => 'sumber data',
            'source_url' => 'tautan sumber',
        ];

        foreach (PopulationStatisticCategory::cases() as $category) {
            $attributes["workbooks.{$category->slug()}"] = "file {$category->value}";
        }

        return $attributes;
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'workbooks.array' => 'Daftar file Excel tidak valid.',
            'workbooks.*.file' => ':Attribute harus berupa file.',
            'workbooks.*.extensions' => ':Attribute harus menggunakan ekstensi .xlsx.',
            'workbooks.*.mimetypes' => ':Attribute harus berupa workbook .xlsx yang valid.',
            'workbooks.*.max' => 'Ukuran :attribute maksimal 2 MB.',
        ];
    }

    /** @return list<string> */
    private function categorySlugs(): array
    {
        return array_map(
            fn (PopulationStatisticCategory $category): string => $category->slug(),
            PopulationStatisticCategory::cases(),
        );
    }
}
