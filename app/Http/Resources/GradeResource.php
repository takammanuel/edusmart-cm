<?php

namespace App\Http\Resources;

use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Grade */
class GradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'matricule' => $this->whenLoaded('student', fn () => $this->student->matricule),
            'value' => (float) $this->value,
            'sequence_id' => $this->sequence_id,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
