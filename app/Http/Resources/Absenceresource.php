<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AbsenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'date'         => $this->date?->format('Y-m-d'),
            'hours'        => $this->hours,
            'is_justified' => $this->is_justified,
            'reason'       => $this->reason,
            'client_uuid'  => $this->client_uuid,
            'recorded_at'  => $this->recorded_at?->toIso8601String(),
            'student'      => $this->whenLoaded('student', fn () => [
                'id'         => $this->student->id,
                'matricule'  => $this->student->matricule,
                'first_name' => $this->student->first_name,
                'last_name'  => $this->student->last_name,
            ]),
            'classroom'    => $this->whenLoaded('classroom', fn () => [
                'id'    => $this->classroom->id,
                'name'  => $this->classroom->name,
                'level' => $this->classroom->level,
            ]),
            'teacher'      => $this->whenLoaded('teacher', fn () => [
                'id'         => $this->teacher->id,
                'first_name' => $this->teacher->first_name,
                'last_name'  => $this->teacher->last_name,
            ]),
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
        ];
    }
}
