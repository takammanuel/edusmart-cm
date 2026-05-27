<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAbsenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id'   => ['required', 'integer', Rule::exists('students', 'id')],
            'classroom_id' => ['required', 'integer', Rule::exists('classrooms', 'id')],
            'teacher_id'   => ['required', 'integer', Rule::exists('teachers', 'id')],
            'date'         => ['required', 'date', 'before_or_equal:today'],
            'hours'        => ['required', 'integer', 'min:1', 'max:8'],
            'is_justified' => ['sometimes', 'boolean'],
            'reason'       => ['nullable', 'string', 'max:500'],
            'client_uuid'  => ['nullable', 'uuid'],
            'recorded_at'  => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required'   => "L'élève est obligatoire.",
            'student_id.exists'     => "L'élève sélectionné est invalide.",
            'classroom_id.required' => 'La classe est obligatoire.',
            'classroom_id.exists'   => 'La classe sélectionnée est invalide.',
            'teacher_id.required'   => "L'enseignant est obligatoire.",
            'teacher_id.exists'     => "L'enseignant sélectionné est invalide.",
            'date.required'         => "La date d'absence est obligatoire.",
            'date.before_or_equal'  => "La date d'absence ne peut pas être dans le futur.",
            'hours.required'        => "Le nombre d'heures est obligatoire.",
            'hours.min'             => "Le nombre d'heures doit être d'au moins 1.",
            'hours.max'             => "Le nombre d'heures ne peut pas dépasser 8 par jour.",
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('is_justified')) {
            $this->merge(['is_justified' => false]);
        }
    }
}
