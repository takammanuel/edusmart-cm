<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Admin\GenerateBulletinRequest;
use App\Models\Classroom;
use App\Models\Sequence;
use App\Models\Student;
use App\Services\BulletinService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;

class BulletinController extends ApiController
{
    public function __construct(private readonly BulletinService $bulletinService) {}

    public function showStudent(GenerateBulletinRequest $request, Student $student): JsonResponse
    {
        if ($student->status !== 'active') {
            return $this->error('Impossible de générer un bulletin pour un élève non actif.', 422);
        }

        $sequence = Sequence::query()->findOrFail($request->integer('sequence_id'));
        $bulletin = $this->bulletinService->buildStudentBulletin($student, $sequence);

        if ($bulletin['summary']['general_average'] === null) {
            return $this->error('Aucune note enregistrée pour cette séquence.', 422);
        }

        return $this->success($bulletin, 'Bulletin généré avec succès');
    }

    public function showClassroom(GenerateBulletinRequest $request, Classroom $classroom): JsonResponse
    {
        $sequence = Sequence::query()->findOrFail($request->integer('sequence_id'));
        $data = $this->bulletinService->buildClassroomBulletins($classroom, $sequence);

        if ($data['bulletins'] === []) {
            return $this->error('Aucun élève actif dans cette classe.', 422);
        }

        return $this->success($data, 'Bulletins de classe générés avec succès');
    }

    public function downloadStudentPdf(GenerateBulletinRequest $request, Student $student)
    {
        if ($student->status !== 'active') {
            return $this->error('Impossible de générer un bulletin pour un élève non actif.', 422);
        }

        $sequence = Sequence::query()->findOrFail($request->integer('sequence_id'));
        $bulletin = $this->bulletinService->buildStudentBulletin($student, $sequence);

        if ($bulletin['summary']['general_average'] === null) {
            return $this->error('Aucune note enregistrée pour cette séquence.', 422);
        }

        $pdf = Pdf::loadView('bulletins.student', ['bulletin' => $bulletin])
            ->setPaper('a4', 'portrait');

        $filename = sprintf(
            'bulletin_%s_%s_seq%d.pdf',
            $bulletin['student']['matricule'],
            $sequence->school_year,
            $sequence->number
        );

        return $pdf->download($filename);
    }
}
