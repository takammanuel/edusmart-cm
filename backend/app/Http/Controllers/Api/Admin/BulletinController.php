<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Http\Request;

class BulletinController extends Controller
{
    /**
     * GET /api/v1/admin/bulletins
     * Retourne la liste des élèves actifs (les bulletins sont générés à la demande)
     */
    public function index(Request $request)
    {
        $query = Student::with('classroom')->where('status', 'active');

        if ($request->classroom_id) {
            $query->where('classroom_id', $request->classroom_id);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->orderBy('last_name')->get(),
        ]);
    }

    /**
     * GET /api/v1/admin/bulletins/student/{student}/download
     */
    public function downloadStudent(Student $student)
    {
        // Génération PDF via barryvdh/laravel-dompdf
        $pdf = app('dompdf.wrapper');
        $pdf->loadHTML($this->buildStudentBulletinHtml($student));

        return $pdf->download("bulletin_{$student->matricule}.pdf");
    }

    /**
     * GET /api/v1/admin/bulletins/classroom/{classroom}/download
     */
    public function downloadClassroom(Classroom $classroom)
    {
        $students = $classroom->students()->where('status', 'active')->get();
        $html     = '';
        foreach ($students as $student) {
            $html .= $this->buildStudentBulletinHtml($student);
            $html .= '<div style="page-break-after: always;"></div>';
        }

        $pdf = app('dompdf.wrapper');
        $pdf->loadHTML($html);

        return $pdf->download("bulletins_{$classroom->name}.pdf");
    }

    private function buildStudentBulletinHtml(Student $student): string
    {
        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; margin: 30px; }
                h1 { color: #1e40af; text-align: center; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #1e40af; color: white; }
            </style>
        </head>
        <body>
            <h1>Bulletin Scolaire — EduSmart CM</h1>
            <p><strong>Élève :</strong> {$student->last_name} {$student->first_name}</p>
            <p><strong>Matricule :</strong> {$student->matricule}</p>
            <p><strong>Classe :</strong> {$student->classroom?->name}</p>
            <p><strong>Année scolaire :</strong> {$student->classroom?->school_year}</p>
            <table>
                <thead>
                    <tr>
                        <th>Matière</th>
                        <th>Note Seq. 1</th>
                        <th>Note Seq. 2</th>
                        <th>Moyenne</th>
                        <th>Coef.</th>
                        <th>Observation</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan='6' style='text-align:center; color:#6b7280;'>Aucune note saisie pour le moment.</td></tr>
                </tbody>
            </table>
        </body>
        </html>";
    }
}
