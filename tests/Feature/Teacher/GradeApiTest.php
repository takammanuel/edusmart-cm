<?php

namespace Tests\Feature\Teacher;

use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Sequence;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeApiTest extends TestCase
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

        $sequence = Sequence::query()->create([
            'number' => 1,
            'name' => '1ère séquence',
            'school_year' => '2025-2026',
            'is_active' => true,
        ]);

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

        return compact('classroom', 'subject', 'teacher', 'sequence', 'studentA', 'studentB');
    }

    public function test_can_bulk_store_grades(): void
    {
        $ctx = $this->seedContext();

        $response = $this->postJson('/api/v1/teacher/grades/bulk', [
            'teacher_id' => $ctx['teacher']->id,
            'classroom_id' => $ctx['classroom']->id,
            'subject_id' => $ctx['subject']->id,
            'sequence_id' => $ctx['sequence']->id,
            'grades' => [
                ['student_id' => $ctx['studentA']->id, 'value' => 15.5],
                ['student_id' => $ctx['studentB']->id, 'value' => 12],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');

        $this->assertDatabaseHas('grades', [
            'student_id' => $ctx['studentA']->id,
            'value' => 15.5,
        ]);
    }

    public function test_rejects_invalid_grade_value(): void
    {
        $ctx = $this->seedContext();

        $response = $this->postJson('/api/v1/teacher/grades/bulk', [
            'teacher_id' => $ctx['teacher']->id,
            'classroom_id' => $ctx['classroom']->id,
            'subject_id' => $ctx['subject']->id,
            'sequence_id' => $ctx['sequence']->id,
            'grades' => [
                ['student_id' => $ctx['studentA']->id, 'value' => 21],
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

        $response = $this->postJson('/api/v1/teacher/grades/bulk', [
            'teacher_id' => $otherTeacher->id,
            'classroom_id' => $ctx['classroom']->id,
            'subject_id' => $ctx['subject']->id,
            'sequence_id' => $ctx['sequence']->id,
            'grades' => [
                ['student_id' => $ctx['studentA']->id, 'value' => 14],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_can_update_existing_grade_via_bulk(): void
    {
        $ctx = $this->seedContext();

        Grade::query()->create([
            'student_id' => $ctx['studentA']->id,
            'classroom_id' => $ctx['classroom']->id,
            'subject_id' => $ctx['subject']->id,
            'teacher_id' => $ctx['teacher']->id,
            'sequence_id' => $ctx['sequence']->id,
            'value' => 10,
        ]);

        $response = $this->postJson('/api/v1/teacher/grades/bulk', [
            'teacher_id' => $ctx['teacher']->id,
            'classroom_id' => $ctx['classroom']->id,
            'subject_id' => $ctx['subject']->id,
            'sequence_id' => $ctx['sequence']->id,
            'grades' => [
                ['student_id' => $ctx['studentA']->id, 'value' => 16],
            ],
        ]);

        $response->assertOk();

        $this->assertDatabaseCount('grades', 1);
        $this->assertDatabaseHas('grades', [
            'student_id' => $ctx['studentA']->id,
            'value' => 16,
        ]);
    }

    public function test_can_list_grades_for_class(): void
    {
        $ctx = $this->seedContext();

        Grade::query()->create([
            'student_id' => $ctx['studentA']->id,
            'classroom_id' => $ctx['classroom']->id,
            'subject_id' => $ctx['subject']->id,
            'teacher_id' => $ctx['teacher']->id,
            'sequence_id' => $ctx['sequence']->id,
            'value' => 14,
        ]);

        $response = $this->getJson('/api/v1/teacher/grades?'.http_build_query([
            'teacher_id' => $ctx['teacher']->id,
            'classroom_id' => $ctx['classroom']->id,
            'subject_id' => $ctx['subject']->id,
            'sequence_id' => $ctx['sequence']->id,
        ]));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.value', 14);
    }

    public function test_rejects_student_not_in_classroom(): void
    {
        $ctx = $this->seedContext();

        $otherClass = Classroom::query()->create([
            'name' => 'Première C',
            'specialty' => 'Sciences',
            'level' => 'Première',
        ]);

        $outsideStudent = Student::query()->create([
            'matricule' => 'ESM-2026-099',
            'first_name' => 'Alice',
            'last_name' => 'Fotso',
            'birth_date' => '2009-01-01',
            'classroom_id' => $otherClass->id,
        ]);

        $response = $this->postJson('/api/v1/teacher/grades/bulk', [
            'teacher_id' => $ctx['teacher']->id,
            'classroom_id' => $ctx['classroom']->id,
            'subject_id' => $ctx['subject']->id,
            'sequence_id' => $ctx['sequence']->id,
            'grades' => [
                ['student_id' => $outsideStudent->id, 'value' => 13],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }
}
