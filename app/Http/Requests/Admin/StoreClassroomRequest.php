<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'specialty' => ['required', 'string', 'max:100'],
            'level' => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la classe est obligatoire.',
            'specialty.required' => 'La spécialité est obligatoire.',
            'level.required' => 'Le niveau est obligatoire.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'specialty' => is_string($this->specialty) ? trim($this->specialty) : $this->specialty,
            'level' => is_string($this->level) ? trim($this->level) : $this->level,
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $exists = \App\Models\Classroom::query()
                ->where('name', $this->input('name'))
                ->where('level', $this->input('level'))
                ->where('specialty', $this->input('specialty'))
                ->exists();

            if ($exists) {
                $validator->errors()->add('name', 'Cette combinaison nom/niveau/spécialité existe déjà.');
            }
        });
    }
}
