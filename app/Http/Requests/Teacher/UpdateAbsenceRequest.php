<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAbsenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_justified' => ['sometimes', 'required', 'boolean'],
            'reason' => ['sometimes', 'nullable', 'string', 'max:255'],
            'hours' => ['sometimes', 'required', 'integer', 'min:1', 'max:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'hours.min' => 'Le nombre d\'heures doit être au minimum 1.',
            'hours.max' => 'Le nombre d\'heures ne peut pas dépasser 8.',
        ];
    }
}
