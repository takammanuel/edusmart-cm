<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\TransferStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $students = Student::query()
            ->with('classroom')
            ->when($request->filled('classroom_id'), fn ($query) => $query->where('classroom_id', $request->integer('classroom_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(function ($builder) use ($search) {
                    $builder->where('matricule', 'like', $search)
                        ->orWhere('first_name', 'like', $search)
                        ->orWhere('last_name', 'like', $search);
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return $this->success(StudentResource::collection($students));
    }

    public function store(StoreStudentRequest $request): JsonResponse
    {
        $student = Student::query()->create([
            ...$request->validated(),
            'status' => 'active',
        ]);

        $student->load('classroom');

        return $this->created(new StudentResource($student));
    }

    public function show(Student $student): JsonResponse
    {
        $student->load('classroom');

        return $this->success(new StudentResource($student));
    }

    public function update(UpdateStudentRequest $request, Student $student): JsonResponse
    {
        if ($student->status !== 'active' && $request->has('classroom_id')) {
            return $this->error(
                'Impossible de modifier la classe d\'un élève transféré ou radié.',
                422
            );
        }

        $student->update($request->validated());
        $student->load('classroom');

        return $this->success(new StudentResource($student), 'Élève mis à jour avec succès');
    }

    public function destroy(Student $student): JsonResponse
    {
        if ($student->grades()->exists() || $student->absences()->exists()) {
            return $this->error(
                'Impossible de supprimer un élève possédant des notes ou absences enregistrées.',
                422
            );
        }

        $student->delete();

        return $this->success(null, 'Élève supprimé avec succès');
    }

    public function transfer(TransferStudentRequest $request, Student $student): JsonResponse
    {
        if ($student->status === 'expelled') {
            return $this->error('Impossible de transférer un élève radié.', 422);
        }

        $targetClassroomId = $request->integer('classroom_id');

        if ($student->classroom_id === $targetClassroomId) {
            return $this->error('L\'élève est déjà inscrit dans cette classe.', 422);
        }

        $student->update([
            'classroom_id' => $targetClassroomId,
            'status' => 'active',
        ]);

        $student->load('classroom');

        return $this->success(new StudentResource($student), 'Élève transféré avec succès');
    }

    public function expel(Student $student): JsonResponse
    {
        if ($student->status === 'expelled') {
            return $this->error('Cet élève est déjà radié.', 422);
        }

        $student->update(['status' => 'expelled']);
        $student->load('classroom');

        return $this->success(new StudentResource($student), 'Élève radié avec succès');
    }
}
