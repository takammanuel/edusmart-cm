<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Absence */
class AbsenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'matricule' => $this->whenLoaded('student', fn () => $this->student->matricule),
            'date' => $this->date?->format('Y-m-d'),
            'hours' => $this->hours,
            'is_justified' => $this->is_justified,
            'reason' => $this->reason,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
