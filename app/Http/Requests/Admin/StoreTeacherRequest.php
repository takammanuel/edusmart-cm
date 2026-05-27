<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'main_subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')],
            'email' => ['nullable', 'email', 'max:150', 'unique:teachers,email'],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'main_subject_id.required' => 'La matière principale est obligatoire.',
            'main_subject_id.exists' => 'La matière sélectionnée est invalide.',
            'email.unique' => 'Cet e-mail est déjà utilisé.',
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['first_name', 'last_name', 'email', 'phone'] as $field) {
            if ($this->has($field) && is_string($this->{$field})) {
                $this->merge([$field => trim($this->{$field})]);
            }
        }
    }
}
