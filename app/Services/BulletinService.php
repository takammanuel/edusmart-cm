<?php

namespace App\Services;

use App\Models\Absence;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Sequence;
use App\Models\Student;
use Illuminate\Support\Collection;

class BulletinService
{
    public function buildStudentBulletin(Student $student, Sequence $sequence): array
    {
        $student->load('classroom');

        $grades = Grade::query()
            ->with(['subject', 'teacher'])
            ->where('student_id', $student->id)
            ->where('sequence_id', $sequence->id)
            ->get();

        $subjectLines = $this->formatSubjectLines($grades);
        $generalAverage = $this->calculateWeightedAverage($subjectLines);

        return [
            'student' => [
                'id' => $student->id,
                'matricule' => $student->matricule,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'birth_date' => $student->birth_date?->format('Y-m-d'),
            ],
            'classroom' => [
                'id' => $student->classroom->id,
                'name' => $student->classroom->name,
                'level' => $student->classroom->level,
                'specialty' => $student->classroom->specialty,
            ],
            'sequence' => [
                'id' => $sequence->id,
                'number' => $sequence->number,
                'name' => $sequence->name,
                'school_year' => $sequence->school_year,
            ],
            'subjects' => $subjectLines,
            'summary' => [
                'general_average' => $generalAverage,
                'mention' => $this->resolveMention($generalAverage),
                'total_coefficients' => collect($subjectLines)->sum('coefficient'),
                'total_absence_hours' => $this->countAbsenceHours($student, $sequence),
            ],
            'generated_at' => now()->toIso8601String(),
        ];
    }

    public function buildClassroomBulletins(Classroom $classroom, Sequence $sequence): array
    {
        $students = Student::query()
            ->where('classroom_id', $classroom->id)
            ->where('status', 'active')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $bulletins = $students->map(fn (Student $student) => $this->buildStudentBulletin($student, $sequence));

        $ranked = $this->applyClassRankings($bulletins);

        return [
            'classroom' => [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'level' => $classroom->level,
                'specialty' => $classroom->specialty,
            ],
            'sequence' => [
                'id' => $sequence->id,
                'number' => $sequence->number,
                'name' => $sequence->name,
                'school_year' => $sequence->school_year,
            ],
            'bulletins' => $ranked,
            'class_average' => $this->calculateClassAverage($ranked),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    private function formatSubjectLines(Collection $grades): array
    {
        return $grades->map(function (Grade $grade) {
            return [
                'subject_id' => $grade->subject_id,
                'subject_name' => $grade->subject->name,
                'subject_code' => $grade->subject->code,
                'coefficient' => $grade->subject->coefficient,
                'grade' => round((float) $grade->value, 2),
                'teacher' => $grade->teacher
                    ? $grade->teacher->first_name.' '.$grade->teacher->last_name
                    : null,
            ];
        })->values()->all();
    }

    private function calculateWeightedAverage(array $subjectLines): ?float
    {
        if ($subjectLines === []) {
            return null;
        }

        $weightedSum = 0;
        $totalCoefficients = 0;

        foreach ($subjectLines as $line) {
            $weightedSum += $line['grade'] * $line['coefficient'];
            $totalCoefficients += $line['coefficient'];
        }

        if ($totalCoefficients === 0) {
            return null;
        }

        return round($weightedSum / $totalCoefficients, 2);
    }

    private function calculateClassAverage(array $bulletins): ?float
    {
        $averages = collect($bulletins)
            ->pluck('summary.general_average')
            ->filter(fn ($avg) => $avg !== null);

        if ($averages->isEmpty()) {
            return null;
        }

        return round($averages->avg(), 2);
    }

    private function applyClassRankings(Collection $bulletins): array
    {
        $sorted = $bulletins
            ->sortByDesc(fn (array $bulletin) => $bulletin['summary']['general_average'] ?? -1)
            ->values();

        $classSize = $sorted->count();
        $rank = 0;
        $previousAverage = null;

        return $sorted->map(function (array $bulletin, int $index) use ($classSize, &$rank, &$previousAverage) {
            $average = $bulletin['summary']['general_average'];

            if ($average !== $previousAverage) {
                $rank = $index + 1;
            }

            $previousAverage = $average;
            $bulletin['summary']['rank'] = $average !== null ? $rank : null;
            $bulletin['summary']['class_size'] = $classSize;

            return $bulletin;
        })->all();
    }

    private function countAbsenceHours(Student $student, Sequence $sequence): int
    {
        return (int) Absence::query()
            ->where('student_id', $student->id)
            ->whereBetween('date', $this->sequenceDateRange($sequence))
            ->sum('hours');
    }

    private function sequenceDateRange(Sequence $sequence): array
    {
        $year = (int) substr($sequence->school_year, 0, 4);
        $startMonth = match ($sequence->number) {
            1 => 9,
            2 => 11,
            3 => 1,
            4 => 3,
            5 => 5,
            default => 6,
        };

        $startYear = in_array($sequence->number, [1, 2], true) ? $year : $year + 1;
        $endMonth = $startMonth + 2;

        return [
            sprintf('%04d-%02d-01', $startYear, $startMonth),
            sprintf('%04d-%02d-28', $startYear, min($endMonth, 12)),
        ];
    }

    public function resolveMention(?float $average): ?string
    {
        if ($average === null) {
            return null;
        }

        return match (true) {
            $average >= 16 => 'Très Bien',
            $average >= 14 => 'Bien',
            $average >= 12 => 'Assez Bien',
            $average >= 10 => 'Passable',
            default => 'Insuffisant',
        };
    }
}
