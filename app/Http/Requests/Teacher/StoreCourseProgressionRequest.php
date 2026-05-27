<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseProgressionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'content' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.before_or_equal' => 'La date doit être égale ou antérieure à aujourd\'hui.',
        ];
    }
}
