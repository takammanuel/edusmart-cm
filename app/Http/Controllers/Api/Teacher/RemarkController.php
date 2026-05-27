<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Teacher\StoreRemarkRequest;
use App\Http\Requests\Teacher\UpdateRemarkRequest;
use App\Http\Resources\RemarkResource;
use App\Models\Classroom;
use App\Models\Remark;
use App\Models\Sequence;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RemarkController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'sequence_id' => ['required', 'integer', 'exists:sequences,id'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'type' => ['nullable', 'in:comportement,travail,assiduité'],
        ]);

        $query = Remark::query()
            ->with(['student:id,matricule,first_name,last_name', 'teacher:id,first_name,last_name'])
            ->where('teacher_id', $validated['teacher_id'])
            ->where('classroom_id', $validated['classroom_id'])
            ->where('sequence_id', $validated['sequence_id']);

        if ($request->filled('student_id')) {
            $query->where('student_id', $validated['student_id']);
        }

        if ($request->filled('type')) {
            $query->where('type', $validated['type']);
        }

        $remarks = $query->orderBy('student_id')->get();

        return $this->success(RemarkResource::collection($remarks));
    }

    public function store(StoreRemarkRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Vérifier que l'enseignant peut noter cette classe
        $teacher = Teacher::query()->findOrFail($data['teacher_id']);
        $isAssigned = $teacher->classrooms()
            ->where('classroom_id', $data['classroom_id'])
            ->exists();

        if (! $isAssigned) {
            return $this->error('Cet enseignant n\'est pas affecté à cette classe.', 422);
        }

        // Vérifier que l'élève est actif dans la classe
        $student = Student::query()
            ->where('id', $data['student_id'])
            ->where('classroom_id', $data['classroom_id'])
            ->where('status', 'active')
            ->first();

        if (! $student) {
            return $this->error('Cet élève n\'est pas actif dans cette classe.', 422);
        }

        $remark = Remark::query()->create($data);
        $remark->load(['student:id,matricule', 'teacher:id,first_name,last_name']);

        return $this->created(new RemarkResource($remark));
    }

    public function show(Remark $remark): JsonResponse
    {
        $remark->load(['student:id,matricule', 'teacher:id,first_name,last_name']);

        return $this->success(new RemarkResource($remark));
    }

    public function update(UpdateRemarkRequest $request, Remark $remark): JsonResponse
    {
        $remark->update($request->validated());
        $remark->load(['student:id,matricule', 'teacher:id,first_name,last_name']);

        return $this->success(new RemarkResource($remark), 'Appréciation mise à jour avec succès');
    }

    public function destroy(Remark $remark): JsonResponse
    {
        $remark->delete();

        return $this->success(null, 'Appréciation supprimée avec succès');
    }

    public function bulkStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'sequence_id' => ['required', 'integer', 'exists:sequences,id'],
            'remarks' => ['required', 'array', 'min:1'],
            'remarks.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'remarks.*.type' => ['required', 'in:comportement,travail,assiduité'],
            'remarks.*.content' => ['required', 'string', 'max:500'],
        ]);

        $teacher = Teacher::query()->findOrFail($validated['teacher_id']);
        $isAssigned = $teacher->classrooms()
            ->where('classroom_id', $validated['classroom_id'])
            ->exists();

        if (! $isAssigned) {
            return $this->error('Cet enseignant n\'est pas affecté à cette classe.', 422);
        }

        $results = [];

        foreach ($validated['remarks'] as $entry) {
            $remark = Remark::query()->create([
                'teacher_id' => $validated['teacher_id'],
                'classroom_id' => $validated['classroom_id'],
                'sequence_id' => $validated['sequence_id'],
                'student_id' => $entry['student_id'],
                'type' => $entry['type'],
                'content' => $entry['content'],
            ]);

            $remark->load(['student:id,matricule', 'teacher:id,first_name,last_name']);
            $results[] = $remark;
        }

        return $this->success(
            RemarkResource::collection(collect($results)),
            count($results).' appréciation(s) enregistrée(s) avec succès'
        );
    }
}
