<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkStoreAbsencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacher_id' => ['required', 'integer', Rule::exists('teachers', 'id')],
            'classroom_id' => ['required', 'integer', Rule::exists('classrooms', 'id')],
            'absences' => ['required', 'array', 'min:1'],
            'absences.*.student_id' => ['required', 'integer', Rule::exists('students', 'id')],
            'absences.*.date' => ['required', 'date', 'before_or_equal:today'],
            'absences.*.hours' => ['required', 'integer', 'min:1', 'max:8'],
            'absences.*.is_justified' => ['required', 'boolean'],
            'absences.*.reason' => ['nullable', 'string', 'max:255'],
            'absences.*.client_uuid' => ['nullable', 'uuid'],
            'absences.*.recorded_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_id.required' => 'L\'enseignant est obligatoire.',
            'classroom_id.required' => 'La classe est obligatoire.',
            'absences.required' => 'La liste des absences est obligatoire.',
            'absences.*.date.before_or_equal' => 'La date d\'absence ne peut pas être dans le futur.',
            'absences.*.hours.min' => 'Le nombre d\'heures doit être au minimum 1.',
            'absences.*.hours.max' => 'Le nombre d\'heures ne peut pas dépasser 8.',
        ];
    }
}
