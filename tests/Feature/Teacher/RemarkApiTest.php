<?php

namespace Tests\Feature\Teacher;

use App\Models\Classroom;
use App\Models\Remark;
use App\Models\Sequence;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemarkApiTest extends TestCase
{
    use RefreshDatabase;

    private function seedRemarkContext(): array
    {
        $classroom = Classroom::query()->create([
            'name' => 'Terminale C',
            'specialty' => 'Sciences',
            'level' => 'Terminale',
        ]);

        $teacher = Teacher::query()->create([
            'first_name' => 'Marie',
            'last_name' => 'Essomba',
            'email' => 'marie@example.cm',
            'main_subject_id' => 1,
        ]);

        $teacher->classrooms()->attach($classroom->id, ['subject_id' => 1]);

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

        return compact('classroom', 'teacher', 'sequence', 'studentA', 'studentB');
    }

    public function test_can_list_remarks(): void
    {
        $context = $this->seedRemarkContext();

        Remark::query()->create([
            'student_id' => $context['studentA']->id,
            'classroom_id' => $context['classroom']->id,
            'teacher_id' => $context['teacher']->id,
            'sequence_id' => $context['sequence']->id,
            'type' => 'comportement',
            'content' => 'Bon comportement',
        ]);

        $response = $this->getJson('/api/v1/teacher/remarks?teacher_id='.$context['teacher']->id.'&classroom_id='.$context['classroom']->id.'&sequence_id='.$context['sequence']->id);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_create_remark(): void
    {
        $context = $this->seedRemarkContext();

        $response = $this->postJson('/api/v1/teacher/remarks', [
            'student_id' => $context['studentA']->id,
            'classroom_id' => $context['classroom']->id,
            'teacher_id' => $context['teacher']->id,
            'sequence_id' => $context['sequence']->id,
            'type' => 'travail',
            'content' => 'Travail exemplaire',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.type', 'travail');

        $this->assertDatabaseHas('remarks', [
            'student_id' => $context['studentA']->id,
            'type' => 'travail',
        ]);
    }

    public function test_can_create_remark_bulk(): void
    {
        $context = $this->seedRemarkContext();

        $response = $this->postJson('/api/v1/teacher/remarks/bulk', [
            'teacher_id' => $context['teacher']->id,
            'classroom_id' => $context['classroom']->id,
            'sequence_id' => $context['sequence']->id,
            'remarks' => [
                [
                    'student_id' => $context['studentA']->id,
                    'type' => 'comportement',
                    'content' => 'Excellent comportement',
                ],
                [
                    'student_id' => $context['studentB']->id,
                    'type' => 'assiduité',
                    'content' => 'Très assidu',
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');

        $this->assertDatabaseCount('remarks', 2);
    }

    public function test_can_show_remark(): void
    {
        $context = $this->seedRemarkContext();

        $remark = Remark::query()->create([
            'student_id' => $context['studentA']->id,
            'classroom_id' => $context['classroom']->id,
            'teacher_id' => $context['teacher']->id,
            'sequence_id' => $context['sequence']->id,
            'type' => 'travail',
            'content' => 'Excellent',
        ]);

        $response = $this->getJson('/api/v1/teacher/remarks/'.$remark->id);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.type', 'travail');
    }

    public function test_can_update_remark(): void
    {
        $context = $this->seedRemarkContext();

        $remark = Remark::query()->create([
            'student_id' => $context['studentA']->id,
            'classroom_id' => $context['classroom']->id,
            'teacher_id' => $context['teacher']->id,
            'sequence_id' => $context['sequence']->id,
            'type' => 'comportement',
            'content' => 'Initial',
        ]);

        $response = $this->putJson('/api/v1/teacher/remarks/'.$remark->id, [
            'content' => 'Très bon comportement',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.content', 'Très bon comportement');
    }

    public function test_can_delete_remark(): void
    {
        $context = $this->seedRemarkContext();

        $remark = Remark::query()->create([
            'student_id' => $context['studentA']->id,
            'classroom_id' => $context['classroom']->id,
            'teacher_id' => $context['teacher']->id,
            'sequence_id' => $context['sequence']->id,
            'type' => 'travail',
            'content' => 'Test',
        ]);

        $response = $this->deleteJson('/api/v1/teacher/remarks/'.$remark->id);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('remarks', ['id' => $remark->id]);
    }

    public function test_cannot_create_remark_for_inactive_student(): void
    {
        $context = $this->seedRemarkContext();

        $context['studentA']->update(['status' => 'expelled']);

        $response = $this->postJson('/api/v1/teacher/remarks', [
            'student_id' => $context['studentA']->id,
            'classroom_id' => $context['classroom']->id,
            'teacher_id' => $context['teacher']->id,
            'sequence_id' => $context['sequence']->id,
            'type' => 'comportement',
            'content' => 'Test',
        ]);

        $response->assertUnprocessable();
    }
}
