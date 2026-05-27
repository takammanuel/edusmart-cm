<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Teacher\BulkStoreGradesRequest;
use App\Http\Resources\GradeResource;
use App\Models\Grade;
use App\Models\Sequence;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradeController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'sequence_id' => ['required', 'integer', 'exists:sequences,id'],
        ]);

        $grades = Grade::query()
            ->with('student:id,matricule,first_name,last_name')
            ->where('teacher_id', $validated['teacher_id'])
            ->where('classroom_id', $validated['classroom_id'])
            ->where('subject_id', $validated['subject_id'])
            ->where('sequence_id', $validated['sequence_id'])
            ->orderBy('student_id')
            ->get(['id', 'student_id', 'value', 'sequence_id', 'updated_at']);

        return $this->success(GradeResource::collection($grades));
    }

    public function bulkStore(BulkStoreGradesRequest $request): JsonResponse
    {
        $data = $request->validated();

        $teacher = Teacher::query()->findOrFail($data['teacher_id']);
        $assignmentError = $this->validateTeacherAssignment(
            $teacher,
            $data['classroom_id'],
            $data['subject_id']
        );

        if ($assignmentError) {
            return $this->error($assignmentError, 422);
        }

        $sequence = Sequence::query()->findOrFail($data['sequence_id']);

        if ($sequence->number < 1 || $sequence->number > 6) {
            return $this->error('La séquence doit être comprise entre 1 et 6.', 422);
        }

        $studentIds = collect($data['grades'])->pluck('student_id')->unique()->values();
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

            foreach ($data['grades'] as $entry) {
                $attributes = [
                    'student_id' => $entry['student_id'],
                    'subject_id' => $data['subject_id'],
                    'sequence_id' => $data['sequence_id'],
                ];

                $values = [
                    'classroom_id' => $data['classroom_id'],
                    'teacher_id' => $data['teacher_id'],
                    'value' => round((float) $entry['value'], 2),
                    'client_uuid' => $entry['client_uuid'] ?? null,
                    'recorded_at' => $entry['recorded_at'] ?? now(),
                ];

                $grade = Grade::query()->updateOrCreate($attributes, $values);
                $grade->load('student:id,matricule');
                $results[] = $grade;
            }

            return $results;
        });

        return $this->success(
            GradeResource::collection(collect($saved)),
            count($saved).' note(s) enregistrée(s) avec succès'
        );
    }

    private function validateTeacherAssignment(Teacher $teacher, int $classroomId, int $subjectId): ?string
    {
        $isAssigned = $teacher->classrooms()
            ->where('classroom_id', $classroomId)
            ->wherePivot('subject_id', $subjectId)
            ->exists();

        if (! $isAssigned) {
            return 'Cet enseignant n\'est pas affecté à cette classe pour cette matière.';
        }

        return null;
    }
}
