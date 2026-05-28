<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classroom;
use App\Models\Absence;
use App\Models\Sequence;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * GET /api/v1/admin/dashboard/stats
     */
    public function stats()
    {
        $studentsByClassroom = Classroom::withCount(['students' => function($q) {
            $q->where('status', 'active');
        }])->get()->map(function($c) {
            return [
                'classroom_id'   => $c->id,
                'classroom_name' => $c->level . ' ' . $c->name,
                'count'          => $c->students_count,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'total_students'        => Student::where('status', 'active')->count(),
                'total_teachers'        => Teacher::where('status', 'active')->count(),
                'total_classrooms'      => Classroom::count(),
                'total_absences'        => Absence::count(),
                'active_sequence'       => Sequence::where('is_active', true)->first()?->name ?? 'Aucune',
                'students_by_classroom' => $studentsByClassroom,
            ],
        ]);
    }

    /**
     * GET /api/v1/admin/dashboard/absences-stats
     */
    public function absencesStats()
    {
        $total      = Absence::count();
        $justified  = Absence::where('justified', true)->count();
        $unjustified = $total - $justified;

        return response()->json([
            'success' => true,
            'data'    => [
                'total_absences'       => $total,
                'justified_absences'   => $justified,
                'unjustified_absences' => $unjustified,
                'absence_rate'         => $total > 0
                    ? round(($unjustified / $total) * 100, 2)
                    : 0,
            ],
        ]);
    }

    /**
     * GET /api/v1/admin/dashboard/grades-stats
     */
    public function gradesStats()
    {
        // Placeholder — pas encore de modèle Grade
        // Retourne des valeurs neutres compatibles avec le frontend
        return response()->json([
            'success' => true,
            'data'    => [
                'average_grade'    => 0,
                'passed_students'  => 0,
                'remedial_students'=> 0,
                'failed_students'  => 0,
                'success_rate'     => 0,
            ],
        ]);
    }
}
