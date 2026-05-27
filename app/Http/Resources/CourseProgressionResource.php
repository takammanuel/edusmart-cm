<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseProgressionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'teacher_id' => $this->teacher_id,
            'teacher' => [
                'id' => $this->teacher?->id,
                'name' => $this->teacher?->first_name . ' ' . $this->teacher?->last_name,
            ],
            'classroom_id' => $this->classroom_id,
            'subject_id' => $this->subject_id,
            'subject' => [
                'id' => $this->subject?->id,
                'name' => $this->subject?->name,
                'code' => $this->subject?->code,
            ],
            'date' => $this->date?->format('Y-m-d'),
            'content' => $this->content,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
