<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Teacher\StoreCourseProgressionRequest;
use App\Http\Requests\Teacher\UpdateCourseProgressionRequest;
use App\Http\Resources\CourseProgressionResource;
use App\Models\CourseProgression;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseProgressionController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $query = CourseProgression::query()
            ->with(['teacher:id,first_name,last_name', 'subject:id,name,code'])
            ->where('teacher_id', $validated['teacher_id'])
            ->where('classroom_id', $validated['classroom_id'])
            ->where('subject_id', $validated['subject_id']);

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $validated['date_from']);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $validated['date_to']);
        }

        $progressions = $query->orderByDesc('date')->get();

        return $this->success(CourseProgressionResource::collection($progressions));
    }

    public function store(StoreCourseProgressionRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Vérifier que l'enseignant est affecté à cette classe pour cette matière
        $teacher = Teacher::query()->findOrFail($data['teacher_id']);
        $isAssigned = $teacher->classrooms()
            ->where('classroom_id', $data['classroom_id'])
            ->wherePivot('subject_id', $data['subject_id'])
            ->exists();

        if (! $isAssigned) {
            return $this->error('Cet enseignant n\'est pas affecté à cette classe pour cette matière.', 422);
        }

        $progression = CourseProgression::query()->create($data);
        $progression->load(['teacher:id,first_name,last_name', 'subject:id,name,code']);

        return $this->created(new CourseProgressionResource($progression));
    }

    public function show(CourseProgression $progression): JsonResponse
    {
        $progression->load(['teacher:id,first_name,last_name', 'subject:id,name,code']);

        return $this->success(new CourseProgressionResource($progression));
    }

    public function update(UpdateCourseProgressionRequest $request, CourseProgression $progression): JsonResponse
    {
        $progression->update($request->validated());
        $progression->load(['teacher:id,first_name,last_name', 'subject:id,name,code']);

        return $this->success(new CourseProgressionResource($progression), 'Progression de cours mise à jour avec succès');
    }

    public function destroy(CourseProgression $progression): JsonResponse
    {
        $progression->delete();

        return $this->success(null, 'Progression de cours supprimée avec succès');
    }

    public function bulkStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'progressions' => ['required', 'array', 'min:1'],
            'progressions.*.date' => ['required', 'date', 'before_or_equal:today'],
            'progressions.*.content' => ['required', 'string', 'max:1000'],
        ]);

        $teacher = Teacher::query()->findOrFail($validated['teacher_id']);
        $isAssigned = $teacher->classrooms()
            ->where('classroom_id', $validated['classroom_id'])
            ->wherePivot('subject_id', $validated['subject_id'])
            ->exists();

        if (! $isAssigned) {
            return $this->error('Cet enseignant n\'est pas affecté à cette classe pour cette matière.', 422);
        }

        $results = [];

        foreach ($validated['progressions'] as $entry) {
            $progression = CourseProgression::query()->create([
                'teacher_id' => $validated['teacher_id'],
                'classroom_id' => $validated['classroom_id'],
                'subject_id' => $validated['subject_id'],
                'date' => $entry['date'],
                'content' => $entry['content'],
            ]);

            $progression->load(['teacher:id,first_name,last_name', 'subject:id,name,code']);
            $results[] = $progression;
        }

        return $this->success(
            CourseProgressionResource::collection(collect($results)),
            count($results).' progression(s) enregistrée(s) avec succès'
        );
    }
}
