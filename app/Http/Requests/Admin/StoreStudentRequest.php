<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'matricule' => ['required', 'string', 'max:50', 'unique:students,matricule'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date', 'before:today'],
            'classroom_id' => ['required', 'integer', Rule::exists('classrooms', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'matricule.required' => 'Le matricule est obligatoire.',
            'matricule.unique' => 'Ce matricule est déjà utilisé.',
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'birth_date.required' => 'La date de naissance est obligatoire.',
            'birth_date.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
            'classroom_id.required' => 'La classe est obligatoire.',
            'classroom_id.exists' => 'La classe sélectionnée est invalide.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $fields = ['matricule', 'first_name', 'last_name'];

        foreach ($fields as $field) {
            if ($this->has($field) && is_string($this->{$field})) {
                $this->merge([$field => trim($this->{$field})]);
            }
        }
    }
}
