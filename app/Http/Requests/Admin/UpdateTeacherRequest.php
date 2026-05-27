<?php

namespace App\Http\Requests\Admin;

use App\Models\Teacher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Teacher $teacher */
        $teacher = $this->route('teacher');

        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:100'],
            'last_name' => ['sometimes', 'required', 'string', 'max:100'],
            'main_subject_id' => ['sometimes', 'required', 'integer', Rule::exists('subjects', 'id')],
            'email' => ['sometimes', 'nullable', 'email', 'max:150', Rule::unique('teachers', 'email')->ignore($teacher->id)],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
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
