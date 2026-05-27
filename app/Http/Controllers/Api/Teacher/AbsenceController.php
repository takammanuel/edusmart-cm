<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Teacher\BulkStoreAbsencesRequest;
use App\Http\Requests\Teacher\UpdateAbsenceRequest;
use App\Http\Resources\AbsenceResource;
use App\Models\Absence;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AbsenceController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'date' => ['nullable', 'date'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $teacher = Teacher::query()->findOrFail($validated['teacher_id']);

        if (! $this->isAssignedToClassroom($teacher, $validated['classroom_id'])) {
            return $this->error('Cet enseignant n\'est pas affecté à cette classe.', 422);
        }

        $absences = Absence::query()
            ->with('student:id,matricule')
            ->where('teacher_id', $validated['teacher_id'])
            ->where('classroom_id', $validated['classroom_id'])
            ->when(isset($validated['date']), fn ($query) => $query->whereDate('date', $validated['date']))
            ->when(isset($validated['date_from']), fn ($query) => $query->whereDate('date', '>=', $validated['date_from']))
            ->when(isset($validated['date_to']), fn ($query) => $query->whereDate('date', '<=', $validated['date_to']))
            ->orderByDesc('date')
            ->orderBy('student_id')
            ->get(['id', 'student_id', 'date', 'hours', 'is_justified', 'reason', 'updated_at']);

        return $this->success(AbsenceResource::collection($absences));
    }

    public function bulkStore(BulkStoreAbsencesRequest $request): JsonResponse
    {
        $data = $request->validated();
        $teacher = Teacher::query()->findOrFail($data['teacher_id']);

        if (! $this->isAssignedToClassroom($teacher, $data['classroom_id'])) {
            return $this->error('Cet enseignant n\'est pas affecté à cette classe.', 422);
        }

        $studentIds = collect($data['absences'])->pluck('student_id')->unique()->values();
        $validStudents = Student::query()
            ->whereIn('id', $studentIds)
            ->where('classroom_id', $data['classroom_id'])
            ->where('status', 'active')
            ->pluck('id');

        if ($validStudents->count() !== $studentIds->count()) {
            return $this->error('Un ou plusieurs élèves ne sont pas actifs dans cette classe.', 422);
        }

        $saved = DB::transaction(function () use ($data) {
            $results = [];

            foreach ($data['absences'] as $entry) {
                $date = Carbon::parse($entry['date'])->toDateString();

                $values = [
                    'teacher_id' => $data['teacher_id'],
                    'hours' => $entry['hours'],
                    'is_justified' => $entry['is_justified'],
                    'reason' => $entry['reason'] ?? null,
                    'client_uuid' => $entry['client_uuid'] ?? null,
                    'recorded_at' => $entry['recorded_at'] ?? now(),
                ];

                $absence = Absence::query()
                    ->where('student_id', $entry['student_id'])
                    ->where('classroom_id', $data['classroom_id'])
                    ->whereDate('date', $date)
                    ->first();

                if ($absence) {
                    $absence->update($values);
                } else {
                    $absence = Absence::query()->create([
                        'student_id' => $entry['student_id'],
                        'classroom_id' => $data['classroom_id'],
                        'date' => $date,
                        ...$values,
                    ]);
                }

                $absence->load('student:id,matricule');
                $results[] = $absence;
            }

            return $results;
        });

        return $this->success(
            AbsenceResource::collection(collect($saved)),
            count($saved).' absence(s) enregistrée(s) avec succès'
        );
    }

    public function update(UpdateAbsenceRequest $request, Absence $absence): JsonResponse
    {
        $absence->update($request->validated());
        $absence->load('student:id,matricule');

        return $this->success(new AbsenceResource($absence), 'Absence mise à jour avec succès');
    }

    private function isAssignedToClassroom(Teacher $teacher, int $classroomId): bool
    {
        return $teacher->classrooms()
            ->where('classroom_id', $classroomId)
            ->exists();
    }
}
