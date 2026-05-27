<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkStoreGradesRequest extends FormRequest
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
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')],
            'sequence_id' => ['required', 'integer', Rule::exists('sequences', 'id')],
            'grades' => ['required', 'array', 'min:1'],
            'grades.*.student_id' => ['required', 'integer', Rule::exists('students', 'id')],
            'grades.*.value' => ['required', 'numeric', 'min:0', 'max:20'],
            'grades.*.client_uuid' => ['nullable', 'uuid'],
            'grades.*.recorded_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_id.required' => 'L\'enseignant est obligatoire.',
            'classroom_id.required' => 'La classe est obligatoire.',
            'subject_id.required' => 'La matière est obligatoire.',
            'sequence_id.required' => 'La séquence est obligatoire.',
            'grades.required' => 'La liste des notes est obligatoire.',
            'grades.*.value.min' => 'La note doit être comprise entre 0 et 20.',
            'grades.*.value.max' => 'La note doit être comprise entre 0 et 20.',
        ];
    }
}
