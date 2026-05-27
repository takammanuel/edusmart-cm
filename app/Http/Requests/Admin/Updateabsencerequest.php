<?php

namespace App\Http\Requests\Admin;

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
            'hours'        => ['sometimes', 'required', 'integer', 'min:1', 'max:8'],
            'is_justified' => ['sometimes', 'required', 'boolean'],
            'reason'       => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'hours.min'          => "Le nombre d'heures doit être d'au moins 1.",
            'hours.max'          => "Le nombre d'heures ne peut pas dépasser 8.",
            'is_justified.required' => 'Le statut de justification est obligatoire.',
        ];
    }
}
