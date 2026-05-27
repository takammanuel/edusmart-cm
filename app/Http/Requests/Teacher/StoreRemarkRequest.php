<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreRemarkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'sequence_id' => ['required', 'integer', 'exists:sequences,id'],
            'type' => ['required', 'in:comportement,travail,assiduité'],
            'content' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Le type d\'appréciation doit être: comportement, travail ou assiduité.',
        ];
    }
}
