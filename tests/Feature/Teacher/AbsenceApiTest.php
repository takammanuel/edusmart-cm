<?php

namespace Tests\Feature\Teacher;

use App\Models\Absence;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsenceApiTest extends TestCase
{
    use RefreshDatabase;

    private function seedContext(): array
    {
        $classroom = Classroom::query()->create([
            'name' => 'Terminale C',
            'specialty' => 'Sciences',
            'level' => 'Terminale',
        ]);

        $subject = Subject::query()->create([
            'name' => 'Mathématiques',
            'code' => 'MATH',
            'coefficient' => 4,
        ]);

        $teacher = Teacher::query()->create([
            'first_name' => 'Marie',
            'last_name' => 'Essomba',
            'main_subject_id' => $subject->id,
        ]);

        $teacher->classrooms()->attach($classroom->id, ['subject_id' => $subject->id]);

        $studentA = Student::query()->create([
            'matricule' => 'ESM-2026-001',
            'first_name' => 'Jean',
            'last_name' => 'Mbarga',
            'birth_date' => '2008-03-12',
            'classroom_id' => $classroom->id,
        ]);

        $studentB = Student::query()->create([
            'matricule' => 'ESM-2026-002',
            'first_name' => 'Paul',
            'last_name' => 'Nkomo',
            'birth_date' => '2008-07-01',
            'classroom_id' => $classroom->id,
        ]);

        return compact('classroom', 'subject', 'teacher', 'studentA', 'studentB');
    }

    public function test_can_bulk_store_absences(): void
    {
        $ctx = $this->seedContext();

        $response = $this->postJson('/api/v1/teacher/absences/bulk', [
            'teacher_id' => $ctx['teacher']->id,
            'classroom_id' => $ctx['classroom']->id,
            'absences' => [
                [
                    'student_id' => $ctx['studentA']->id,
                    'date' => '2026-05-20',
                    'hours' => 2,
                    'is_justified' => false,
                ],
                [
                    'student_id' => $ctx['studentB']->id,
                    'date' => '2026-05-20',
                    'hours' => 1,
                    'is_justified' => true,
                    'reason' => 'Visite médicale',
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');

        $this->assertDatabaseHas('absences', [
            'student_id' => $ctx['studentA']->id,
            'hours' => 2,
            'is_justified' => false,
        ]);
    }

    public function test_rejects_future_absence_date(): void
    {
        $ctx = $this->seedContext();

        $response = $this->postJson('/api/v1/teacher/absences/bulk', [
            'teacher_id' => $ctx['teacher']->id,
            'classroom_id' => $ctx['classroom']->id,
            'absences' => [
                [
                    'student_id' => $ctx['studentA']->id,
                    'date' => now()->addDay()->format('Y-m-d'),
                    'hours' => 2,
                    'is_justified' => false,
                ],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_rejects_unassigned_teacher(): void
    {
        $ctx = $this->seedContext();

        $otherTeacher = Teacher::query()->create([
            'first_name' => 'Paul',
            'last_name' => 'Atangana',
            'main_subject_id' => $ctx['subject']->id,
        ]);

        $response = $this->postJson('/api/v1/teacher/absences/bulk', [
            'teacher_id' => $otherTeacher->id,
            'classroom_id' => $ctx['classroom']->id,
            'absences' => [
                [
                    'student_id' => $ctx['studentA']->id,
                    'date' => '2026-05-20',
                    'hours' => 1,
                    'is_justified' => false,
                ],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_can_update_absence_justification(): void
    {
        $ctx = $this->seedContext();

        $absence = Absence::query()->create([
            'student_id' => $ctx['studentA']->id,
            'classroom_id' => $ctx['classroom']->id,
            'teacher_id' => $ctx['teacher']->id,
            'date' => '2026-05-20',
            'hours' => 2,
            'is_justified' => false,
        ]);

        $response = $this->patchJson("/api/v1/teacher/absences/{$absence->id}", [
            'is_justified' => true,
            'reason' => 'Certificat médical présenté',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.is_justified', true)
            ->assertJsonPath('data.reason', 'Certificat médical présenté');
    }

    public function test_can_list_absences_by_date(): void
    {
        $ctx = $this->seedContext();

        Absence::query()->create([
            'student_id' => $ctx['studentA']->id,
            'classroom_id' => $ctx['classroom']->id,
            'teacher_id' => $ctx['teacher']->id,
            'date' => '2026-05-20',
            'hours' => 2,
            'is_justified' => false,
        ]);

        Absence::query()->create([
            'student_id' => $ctx['studentB']->id,
            'classroom_id' => $ctx['classroom']->id,
            'teacher_id' => $ctx['teacher']->id,
            'date' => '2026-05-21',
            'hours' => 1,
            'is_justified' => true,
        ]);

        $response = $this->getJson('/api/v1/teacher/absences?'.http_build_query([
            'teacher_id' => $ctx['teacher']->id,
            'classroom_id' => $ctx['classroom']->id,
            'date' => '2026-05-20',
        ]));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.hours', 2);
    }

    public function test_updates_existing_absence_for_same_day(): void
    {
        $ctx = $this->seedContext();

        Absence::query()->create([
            'student_id' => $ctx['studentA']->id,
            'classroom_id' => $ctx['classroom']->id,
            'teacher_id' => $ctx['teacher']->id,
            'date' => '2026-05-20',
            'hours' => 1,
            'is_justified' => false,
        ]);

        $response = $this->postJson('/api/v1/teacher/absences/bulk', [
            'teacher_id' => $ctx['teacher']->id,
            'classroom_id' => $ctx['classroom']->id,
            'absences' => [
                [
                    'student_id' => $ctx['studentA']->id,
                    'date' => '2026-05-20',
                    'hours' => 3,
                    'is_justified' => true,
                ],
            ],
        ]);

        $response->assertOk();

        $this->assertDatabaseCount('absences', 1);
        $this->assertDatabaseHas('absences', [
            'student_id' => $ctx['studentA']->id,
            'hours' => 3,
            'is_justified' => true,
        ]);
    }
}
