<?php

namespace App\Http\Requests\Admin;

use App\Models\ThematicStatisticDataset;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmThematicStatisticImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ThematicStatisticDataset::class) ?? false;
    }

    /** @return array<string, array<mixed>> */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'size:40', 'regex:/^[a-f0-9]+$/'],
            'publish_now' => ['sometimes', 'boolean'],
        ];
    }
}
