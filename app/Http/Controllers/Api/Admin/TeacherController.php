<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Admin\AssignTeacherClassroomRequest;
use App\Http\Requests\Admin\StoreTeacherRequest;
use App\Http\Requests\Admin\UpdateTeacherRequest;
use App\Http\Resources\TeacherResource;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $teachers = Teacher::query()
            ->with(['mainSubject', 'classrooms'])
            ->withCount('classrooms')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(function ($builder) use ($search) {
                    $builder->where('first_name', 'like', $search)
                        ->orWhere('last_name', 'like', $search)
                        ->orWhere('email', 'like', $search);
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $subjects = Subject::query()
            ->whereIn('id', $teachers->flatMap(fn (Teacher $teacher) => $teacher->classrooms->pluck('pivot.subject_id'))->unique())
            ->get()
            ->keyBy('id');

        $request->attributes->set('subjects_by_id', $subjects);

        return $this->success(TeacherResource::collection($teachers));
    }

    public function store(StoreTeacherRequest $request): JsonResponse
    {
        $teacher = Teacher::query()->create($request->validated());
        $teacher->load(['mainSubject', 'classrooms']);

        return $this->created(new TeacherResource($teacher));
    }

    public function show(Teacher $teacher): JsonResponse
    {
        $teacher->load(['mainSubject', 'classrooms']);

        return $this->success(new TeacherResource($teacher));
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher): JsonResponse
    {
        $teacher->update($request->validated());
        $teacher->load(['mainSubject', 'classrooms']);

        return $this->success(new TeacherResource($teacher), 'Enseignant mis à jour avec succès');
    }

    public function destroy(Teacher $teacher): JsonResponse
    {
        if ($teacher->grades()->exists() || $teacher->absences()->exists()) {
            return $this->error(
                'Impossible de supprimer un enseignant possédant des notes ou absences enregistrées.',
                422
            );
        }

        $teacher->classrooms()->detach();
        $teacher->delete();

        return $this->success(null, 'Enseignant supprimé avec succès');
    }

    public function assignClassroom(AssignTeacherClassroomRequest $request, Teacher $teacher): JsonResponse
    {
        $classroomId = $request->integer('classroom_id');
        $subjectId = $request->integer('subject_id');

        $alreadyAssigned = $teacher->classrooms()
            ->where('classroom_id', $classroomId)
            ->wherePivot('subject_id', $subjectId)
            ->exists();

        if ($alreadyAssigned) {
            return $this->error('Cet enseignant est déjà affecté à cette classe pour cette matière.', 422);
        }

        $teacher->classrooms()->attach($classroomId, ['subject_id' => $subjectId]);
        $teacher->load(['mainSubject', 'classrooms']);

        return $this->success(new TeacherResource($teacher), 'Affectation enregistrée avec succès');
    }

    public function unassignClassroom(AssignTeacherClassroomRequest $request, Teacher $teacher): JsonResponse
    {
        $classroomId = $request->integer('classroom_id');
        $subjectId = $request->integer('subject_id');

        $assignment = $teacher->classrooms()
            ->where('classroom_id', $classroomId)
            ->wherePivot('subject_id', $subjectId)
            ->exists();

        if (! $assignment) {
            return $this->notFound('Affectation introuvable pour cet enseignant.');
        }

        DB::table('classroom_teacher')
            ->where('teacher_id', $teacher->id)
            ->where('classroom_id', $classroomId)
            ->where('subject_id', $subjectId)
            ->delete();

        $teacher->load(['mainSubject', 'classrooms']);

        return $this->success(new TeacherResource($teacher), 'Affectation retirée avec succès');
    }
}
