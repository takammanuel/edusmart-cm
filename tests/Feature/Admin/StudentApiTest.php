<?php

namespace Tests\Feature\Admin;

use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Sequence;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentApiTest extends TestCase
{
    use RefreshDatabase;

    private function createClassroom(string $name = 'Terminale C'): Classroom
    {
        return Classroom::query()->create([
            'name' => $name,
            'specialty' => 'Sciences',
            'level' => 'Terminale',
        ]);
    }

    public function test_can_enroll_student(): void
    {
        $classroom = $this->createClassroom();

        $response = $this->postJson('/api/v1/admin/students', [
            'matricule' => 'ESM-2026-001',
            'first_name' => 'Jean',
            'last_name' => 'Mbarga',
            'birth_date' => '2008-03-12',
            'classroom_id' => $classroom->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.classroom.name', 'Terminale C');

        $this->assertDatabaseHas('students', [
            'matricule' => 'ESM-2026-001',
            'status' => 'active',
        ]);
    }

    public function test_cannot_enroll_with_duplicate_matricule(): void
    {
        $classroom = $this->createClassroom();

        Student::query()->create([
            'matricule' => 'ESM-2026-001',
            'first_name' => 'Jean',
            'last_name' => 'Mbarga',
            'birth_date' => '2008-03-12',
            'classroom_id' => $classroom->id,
        ]);

        $response = $this->postJson('/api/v1/admin/students', [
            'matricule' => 'ESM-2026-001',
            'first_name' => 'Paul',
            'last_name' => 'Nkomo',
            'birth_date' => '2008-07-01',
            'classroom_id' => $classroom->id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_can_list_students_by_classroom(): void
    {
        $classroomA = $this->createClassroom('Terminale C');
        $classroomB = $this->createClassroom('Première C');

        Student::query()->create([
            'matricule' => 'ESM-2026-001',
            'first_name' => 'Jean',
            'last_name' => 'Mbarga',
            'birth_date' => '2008-03-12',
            'classroom_id' => $classroomA->id,
        ]);

        Student::query()->create([
            'matricule' => 'ESM-2026-002',
            'first_name' => 'Paul',
            'last_name' => 'Nkomo',
            'birth_date' => '2008-07-01',
            'classroom_id' => $classroomB->id,
        ]);

        $response = $this->getJson('/api/v1/admin/students?classroom_id='.$classroomA->id);

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_transfer_student(): void
    {
        $source = $this->createClassroom('Terminale C');
        $target = $this->createClassroom('Première C');

        $student = Student::query()->create([
            'matricule' => 'ESM-2026-001',
            'first_name' => 'Jean',
            'last_name' => 'Mbarga',
            'birth_date' => '2008-03-12',
            'classroom_id' => $source->id,
        ]);

        $response = $this->postJson("/api/v1/admin/students/{$student->id}/transfer", [
            'classroom_id' => $target->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.classroom.name', 'Première C')
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'classroom_id' => $target->id,
            'status' => 'active',
        ]);
    }

    public function test_can_expel_student(): void
    {
        $classroom = $this->createClassroom();

        $student = Student::query()->create([
            'matricule' => 'ESM-2026-001',
            'first_name' => 'Jean',
            'last_name' => 'Mbarga',
            'birth_date' => '2008-03-12',
            'classroom_id' => $classroom->id,
        ]);

        $response = $this->postJson("/api/v1/admin/students/{$student->id}/expel");

        $response->assertOk()
            ->assertJsonPath('data.status', 'expelled');
    }

    public function test_cannot_transfer_expelled_student(): void
    {
        $source = $this->createClassroom('Terminale C');
        $target = $this->createClassroom('Première C');

        $student = Student::query()->create([
            'matricule' => 'ESM-2026-001',
            'first_name' => 'Jean',
            'last_name' => 'Mbarga',
            'birth_date' => '2008-03-12',
            'classroom_id' => $source->id,
            'status' => 'expelled',
        ]);

        $response = $this->postJson("/api/v1/admin/students/{$student->id}/transfer", [
            'classroom_id' => $target->id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_cannot_delete_student_with_academic_records(): void
    {
        $classroom = $this->createClassroom();
        $subject = Subject::query()->create(['name' => 'Mathématiques', 'code' => 'MATH']);
        $teacher = Teacher::query()->create([
            'first_name' => 'Marie',
            'last_name' => 'Essomba',
            'main_subject_id' => $subject->id,
        ]);
        $sequence = Sequence::query()->create([
            'number' => 1,
            'name' => '1ère séquence',
            'school_year' => '2025-2026',
            'is_active' => true,
        ]);

        $student = Student::query()->create([
            'matricule' => 'ESM-2026-001',
            'first_name' => 'Jean',
            'last_name' => 'Mbarga',
            'birth_date' => '2008-03-12',
            'classroom_id' => $classroom->id,
        ]);

        Grade::query()->create([
            'student_id' => $student->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'sequence_id' => $sequence->id,
            'value' => 15,
        ]);

        $response = $this->deleteJson("/api/v1/admin/students/{$student->id}");

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_can_delete_student_without_records(): void
    {
        $classroom = $this->createClassroom();

        $student = Student::query()->create([
            'matricule' => 'ESM-2026-001',
            'first_name' => 'Jean',
            'last_name' => 'Mbarga',
            'birth_date' => '2008-03-12',
            'classroom_id' => $classroom->id,
        ]);

        $response = $this->deleteJson("/api/v1/admin/students/{$student->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }
}
