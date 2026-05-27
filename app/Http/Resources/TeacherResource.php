<?php

namespace App\Http\Resources;

use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/** @mixin Teacher */
class TeacherResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'main_subject' => new SubjectResource($this->whenLoaded('mainSubject')),
            'main_subject_id' => $this->when(! $this->relationLoaded('mainSubject'), $this->main_subject_id),
            'assignments' => $this->when($this->relationLoaded('classrooms'), function () use ($request) {
                return $this->formatAssignments($request);
            }),
            'classrooms_count' => $this->whenCounted('classrooms'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function formatAssignments(Request $request): Collection
    {
        $subjects = $request->attributes->get('subjects_by_id');

        if (! $subjects instanceof Collection) {
            $subjects = Subject::query()
                ->whereIn('id', $this->classrooms->pluck('pivot.subject_id'))
                ->get()
                ->keyBy('id');
        }

        return $this->classrooms->map(function ($classroom) use ($subjects) {
            return [
                'classroom' => [
                    'id' => $classroom->id,
                    'name' => $classroom->name,
                    'level' => $classroom->level,
                    'specialty' => $classroom->specialty,
                ],
                'subject' => new SubjectResource($subjects->get($classroom->pivot->subject_id)),
            ];
        })->values();
    }
}
