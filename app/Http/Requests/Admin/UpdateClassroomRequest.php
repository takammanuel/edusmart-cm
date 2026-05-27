<?php

namespace App\Http\Requests\Admin;

use App\Models\Classroom;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'specialty' => ['sometimes', 'required', 'string', 'max:100'],
            'level' => ['sometimes', 'required', 'string', 'max:50'],
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
        $fields = ['name', 'specialty', 'level'];

        foreach ($fields as $field) {
            if ($this->has($field) && is_string($this->{$field})) {
                $this->merge([$field => trim($this->{$field})]);
            }
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var Classroom|null $classroom */
            $classroom = $this->route('classroom');

            if (! $classroom instanceof Classroom) {
                return;
            }

            $name = $this->input('name', $classroom->name);
            $level = $this->input('level', $classroom->level);
            $specialty = $this->input('specialty', $classroom->specialty);

            $exists = Classroom::query()
                ->where('name', $name)
                ->where('level', $level)
                ->where('specialty', $specialty)
                ->where('id', '!=', $classroom->id)
                ->exists();

            if ($exists) {
                $validator->errors()->add('name', 'Cette combinaison nom/niveau/spécialité existe déjà.');
            }
        });
    }
}
