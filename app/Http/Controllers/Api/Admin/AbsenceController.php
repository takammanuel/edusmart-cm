<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Admin\StoreAbsenceRequest;
use App\Http\Requests\Admin\UpdateAbsenceRequest;
use App\Http\Resources\AbsenceResource;
use App\Models\Absence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbsenceController extends ApiController
{
    /**
     * Liste des absences avec filtres optionnels.
     * GET /api/admin/absences?student_id=&classroom_id=&date_from=&date_to=&is_justified=
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'student_id'   => ['nullable', 'integer', 'exists:students,id'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'teacher_id'   => ['nullable', 'integer', 'exists:teachers,id'],
            'date_from'    => ['nullable', 'date'],
            'date_to'      => ['nullable', 'date', 'after_or_equal:date_from'],
            'is_justified' => ['nullable', 'boolean'],
        ]);

        $absences = Absence::query()
            ->with(['student', 'classroom', 'teacher'])
            ->when($request->filled('student_id'),
                fn ($q) => $q->where('student_id', $request->integer('student_id')))
            ->when($request->filled('classroom_id'),
                fn ($q) => $q->where('classroom_id', $request->integer('classroom_id')))
            ->when($request->filled('teacher_id'),
                fn ($q) => $q->where('teacher_id', $request->integer('teacher_id')))
            ->when($request->filled('date_from'),
                fn ($q) => $q->whereDate('date', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'),
                fn ($q) => $q->whereDate('date', '<=', $request->input('date_to')))
            ->when($request->has('is_justified'),
                fn ($q) => $q->where('is_justified', $request->boolean('is_justified')))
            ->orderByDesc('date')
            ->orderBy('student_id')
            ->get();

        return $this->success(AbsenceResource::collection($absences));
    }

    /**
     * Enregistrer une absence.
     * POST /api/admin/absences
     */
    public function store(StoreAbsenceRequest $request): JsonResponse
    {
        // Empêcher le doublon : même élève, même date, même enseignant
        $exists = Absence::query()
            ->where('student_id',   $request->integer('student_id'))
            ->where('classroom_id', $request->integer('classroom_id'))
            ->where('teacher_id',   $request->integer('teacher_id'))
            ->whereDate('date',     $request->input('date'))
            ->exists();

        if ($exists) {
            return $this->error(
                'Une absence a déjà été enregistrée pour cet élève à cette date pour ce cours.',
                422
            );
        }

        $absence = Absence::query()->create($request->validated());
        $absence->load(['student', 'classroom', 'teacher']);

        return $this->created(new AbsenceResource($absence));
    }

    /**
     * Afficher une absence.
     * GET /api/admin/absences/{absence}
     */
    public function show(Absence $absence): JsonResponse
    {
        $absence->load(['student', 'classroom', 'teacher']);

        return $this->success(new AbsenceResource($absence));
    }

    /**
     * Mettre à jour une absence (justification, raison, heures).
     * PATCH /api/admin/absences/{absence}
     */
    public function update(UpdateAbsenceRequest $request, Absence $absence): JsonResponse
    {
        $absence->update($request->validated());
        $absence->load(['student', 'classroom', 'teacher']);

        return $this->success(new AbsenceResource($absence), 'Absence mise à jour avec succès');
    }

    /**
     * Supprimer une absence.
     * DELETE /api/admin/absences/{absence}
     */
    public function destroy(Absence $absence): JsonResponse
    {
        $absence->delete();

        return $this->success(null, 'Absence supprimée avec succès');
    }

    /**
     * Résumé des absences d'un élève (total heures, justifiées / non justifiées).
     * GET /api/admin/absences/summary?student_id=&school_year=
     */
    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'student_id'  => ['required', 'integer', 'exists:students,id'],
            'school_year' => ['nullable', 'string', 'max:9'], // ex: 2025-2026
        ]);

        $query = Absence::query()
            ->where('student_id', $request->integer('student_id'));

        if ($request->filled('school_year')) {
            [$startYear] = explode('-', $request->input('school_year'));
            $query->whereBetween('date', [
                $startYear.'-09-01',
                ((int) $startYear + 1).'-07-31',
            ]);
        }

        $absences = $query->get();

        return $this->success([
            'student_id'          => $request->integer('student_id'),
            'total_hours'         => (int) $absences->sum('hours'),
            'justified_hours'     => (int) $absences->where('is_justified', true)->sum('hours'),
            'unjustified_hours'   => (int) $absences->where('is_justified', false)->sum('hours'),
            'total_occurrences'   => $absences->count(),
            'justified_count'     => $absences->where('is_justified', true)->count(),
            'unjustified_count'   => $absences->where('is_justified', false)->count(),
        ]);
    }
}
