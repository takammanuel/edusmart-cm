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

class TeacherApiTest extends TestCase
{
    use RefreshDatabase;

    private function createSubject(): Subject
    {
        return Subject::query()->create([
            'name' => 'Mathématiques',
            'code' => 'MATH',
        ]);
    }

    private function createClassroom(): Classroom
    {
        return Classroom::query()->create([
            'name' => 'Terminale C',
            'specialty' => 'Sciences',
            'level' => 'Terminale',
        ]);
    }

    public function test_can_create_teacher(): void
    {
        $subject = $this->createSubject();

        $response = $this->postJson('/api/v1/admin/teachers', [
            'first_name' => 'Marie',
            'last_name' => 'Essomba',
            'main_subject_id' => $subject->id,
            'email' => 'marie.essomba@edusmart.cm',
            'phone' => '677000001',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.main_subject.code', 'MATH');

        $this->assertDatabaseHas('teachers', [
            'email' => 'marie.essomba@edusmart.cm',
        ]);
    }

    public function test_can_assign_teacher_to_classroom(): void
    {
        $subject = $this->createSubject();
        $classroom = $this->createClassroom();

        $teacher = Teacher::query()->create([
            'first_name' => 'Marie',
            'last_name' => 'Essomba',
            'main_subject_id' => $subject->id,
        ]);

        $response = $this->postJson("/api/v1/admin/teachers/{$teacher->id}/assignments", [
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.assignments');

        $this->assertDatabaseHas('classroom_teacher', [
            'teacher_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
        ]);
    }

    public function test_cannot_assign_duplicate_assignment(): void
    {
        $subject = $this->createSubject();
        $classroom = $this->createClassroom();

        $teacher = Teacher::query()->create([
            'first_name' => 'Marie',
            'last_name' => 'Essomba',
            'main_subject_id' => $subject->id,
        ]);

        $teacher->classrooms()->attach($classroom->id, ['subject_id' => $subject->id]);

        $response = $this->postJson("/api/v1/admin/teachers/{$teacher->id}/assignments", [
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_can_unassign_teacher_from_classroom(): void
    {
        $math = $this->createSubject();
        $french = Subject::query()->create(['name' => 'Français', 'code' => 'FR']);
        $classroom = $this->createClassroom();

        $teacher = Teacher::query()->create([
            'first_name' => 'Marie',
            'last_name' => 'Essomba',
            'main_subject_id' => $math->id,
        ]);

        $teacher->classrooms()->attach($classroom->id, ['subject_id' => $math->id]);
        $teacher->classrooms()->attach($classroom->id, ['subject_id' => $french->id]);

        $response = $this->deleteJson("/api/v1/admin/teachers/{$teacher->id}/assignments", [
            'classroom_id' => $classroom->id,
            'subject_id' => $math->id,
        ]);

        $response->assertOk()
            ->assertJsonCount(1, 'data.assignments');

        $this->assertDatabaseMissing('classroom_teacher', [
            'teacher_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $math->id,
        ]);

        $this->assertDatabaseHas('classroom_teacher', [
            'teacher_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $french->id,
        ]);
    }

    public function test_can_update_teacher(): void
    {
        $subject = $this->createSubject();

        $teacher = Teacher::query()->create([
            'first_name' => 'Marie',
            'last_name' => 'Essomba',
            'main_subject_id' => $subject->id,
        ]);

        $response = $this->putJson("/api/v1/admin/teachers/{$teacher->id}", [
            'phone' => '677000099',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.phone', '677000099');
    }

    public function test_cannot_delete_teacher_with_grades(): void
    {
        $subject = $this->createSubject();
        $classroom = $this->createClassroom();
        $sequence = Sequence::query()->create([
            'number' => 1,
            'name' => '1ère séquence',
            'school_year' => '2025-2026',
            'is_active' => true,
        ]);

        $teacher = Teacher::query()->create([
            'first_name' => 'Marie',
            'last_name' => 'Essomba',
            'main_subject_id' => $subject->id,
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
            'value' => 14,
        ]);

        $response = $this->deleteJson("/api/v1/admin/teachers/{$teacher->id}");

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_can_delete_teacher_without_records(): void
    {
        $subject = $this->createSubject();

        $teacher = Teacher::query()->create([
            'first_name' => 'Marie',
            'last_name' => 'Essomba',
            'main_subject_id' => $subject->id,
        ]);

        $response = $this->deleteJson("/api/v1/admin/teachers/{$teacher->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('teachers', ['id' => $teacher->id]);
    }
}
