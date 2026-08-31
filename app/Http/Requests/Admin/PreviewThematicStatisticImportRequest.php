<?php

namespace App\Http\Requests\Admin;

use App\Models\ThematicStatisticDataset;
use App\ThematicStatisticType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PreviewThematicStatisticImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ThematicStatisticDataset::class) ?? false;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        $slugs = array_map(fn (ThematicStatisticType $type): string => $type->slug(), ThematicStatisticType::cases());
        $rules = [
            'workbooks' => ['nullable', 'array:'.implode(',', $slugs)],
            'period' => ['required', 'string', 'max:80'],
            'source' => ['required', 'string', 'max:180'],
            'source_url' => ['nullable', 'url:http,https', 'max:2048'],
        ];

        foreach (ThematicStatisticType::cases() as $type) {
            $rules['workbooks.'.$type->slug()] = $type->isHtmlSpreadsheet()
                ? ['nullable', 'file', 'extensions:xls', 'mimetypes:application/vnd.ms-excel,text/html,text/plain,application/octet-stream', 'max:1024']
                : ['nullable', 'file', 'extensions:xlsx', 'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip,application/octet-stream', 'max:3072'];
        }

        return $rules;
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $hasWorkbook = collect(ThematicStatisticType::cases())
                    ->contains(fn (ThematicStatisticType $type): bool => $this->hasFile('workbooks.'.$type->slug()));

                if (! $hasWorkbook) {
                    $validator->errors()->add('workbooks', 'Pilih sedikitnya satu workbook statistik tematik.');
                }
            },
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        $attributes = [
            'workbooks' => 'workbook statistik tematik',
            'period' => 'periode',
            'source' => 'sumber data',
            'source_url' => 'tautan sumber',
        ];

        foreach (ThematicStatisticType::cases() as $type) {
            $attributes['workbooks.'.$type->slug()] = 'file '.$type->title();
        }

        return $attributes;
    }
}
