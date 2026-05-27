<?php

namespace App\Http\Requests\Admin;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Student $student */
        $student = $this->route('student');

        return [
            'matricule' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('students', 'matricule')->ignore($student->id)],
            'first_name' => ['sometimes', 'required', 'string', 'max:100'],
            'last_name' => ['sometimes', 'required', 'string', 'max:100'],
            'birth_date' => ['sometimes', 'required', 'date', 'before:today'],
            'classroom_id' => ['sometimes', 'required', 'integer', Rule::exists('classrooms', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'matricule.unique' => 'Ce matricule est déjà utilisé.',
            'birth_date.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
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
