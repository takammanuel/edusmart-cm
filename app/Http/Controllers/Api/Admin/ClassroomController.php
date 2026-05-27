<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Admin\StoreClassroomRequest;
use App\Http\Requests\Admin\UpdateClassroomRequest;
use App\Http\Resources\ClassroomResource;
use App\Models\Classroom;
use Illuminate\Http\JsonResponse;

class ClassroomController extends ApiController
{
    public function index(): JsonResponse
    {
        $classrooms = Classroom::query()
            ->withCount('students')
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        return $this->success(ClassroomResource::collection($classrooms));
    }

    public function store(StoreClassroomRequest $request): JsonResponse
    {
        $classroom = Classroom::query()->create($request->validated());

        return $this->created(new ClassroomResource($classroom));
    }

    public function show(Classroom $classroom): JsonResponse
    {
        $classroom->loadCount('students');

        return $this->success(new ClassroomResource($classroom));
    }

    public function update(UpdateClassroomRequest $request, Classroom $classroom): JsonResponse
    {
        $classroom->update($request->validated());
        $classroom->loadCount('students');

        return $this->success(new ClassroomResource($classroom), 'Classe mise à jour avec succès');
    }

    public function destroy(Classroom $classroom): JsonResponse
    {
        if ($classroom->students()->exists()) {
            return $this->error(
                'Impossible de supprimer une classe contenant des élèves inscrits.',
                422
            );
        }

        $classroom->delete();

        return $this->success(null, 'Classe supprimée avec succès');
    }
}
