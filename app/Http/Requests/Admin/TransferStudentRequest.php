<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classroom_id' => ['required', 'integer', Rule::exists('classrooms', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'classroom_id.required' => 'La classe de destination est obligatoire.',
            'classroom_id.exists' => 'La classe de destination est invalide.',
        ];
    }
}
