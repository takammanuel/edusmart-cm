<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RemarkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'student' => [
                'id' => $this->student?->id,
                'matricule' => $this->student?->matricule,
                'first_name' => $this->student?->first_name,
                'last_name' => $this->student?->last_name,
            ],
            'teacher' => [
                'id' => $this->teacher?->id,
                'name' => $this->teacher?->first_name . ' ' . $this->teacher?->last_name,
            ],
            'type' => $this->type,
            'content' => $this->content,
            'sequence_id' => $this->sequence_id,
            'classroom_id' => $this->classroom_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
