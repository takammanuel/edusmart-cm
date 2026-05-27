<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateBulletinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sequence_id' => ['required', 'integer', Rule::exists('sequences', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'sequence_id.required' => 'La séquence est obligatoire.',
            'sequence_id.exists' => 'La séquence sélectionnée est invalide.',
        ];
    }
}
