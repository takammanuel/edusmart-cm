<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseProgressionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['sometimes', 'date', 'before_or_equal:today'],
            'content' => ['sometimes', 'string', 'max:1000'],
        ];
    }
}
