<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignTeacherClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classroom_id' => ['required', 'integer', Rule::exists('classrooms', 'id')],
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'classroom_id.required' => 'La classe est obligatoire.',
            'classroom_id.exists' => 'La classe sélectionnée est invalide.',
            'subject_id.required' => 'La matière est obligatoire.',
            'subject_id.exists' => 'La matière sélectionnée est invalide.',
        ];
    }
}
