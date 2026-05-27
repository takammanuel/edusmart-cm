<?php

namespace Tests\Feature\Admin;

use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Sequence;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\BulletinService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulletinApiTest extends TestCase
{
    use RefreshDatabase;

    private function seedBulletinContext(): array
    {
        $classroom = Classroom::query()->create([
            'name' => 'Terminale C',
            'specialty' => 'Sciences',
            'level' => 'Terminale',
        ]);

        $math = Subject::query()->create(['name' => 'Mathématiques', 'code' => 'MATH', 'coefficient' => 4]);
        $french = Subject::query()->create(['name' => 'Français', 'code' => 'FR', 'coefficient' => 4]);

        $teacher = Teacher::query()->create([
            'first_name' => 'Marie',
            'last_name' => 'Essomba',
            'main_subject_id' => $math->id,
        ]);

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

        foreach ([
            [$studentA, 16, $math],
            [$studentA, 12, $french],
            [$studentB, 10, $math],
            [$studentB, 11, $french],
        ] as [$student, $value, $subject]) {
            Grade::query()->create([
                'student_id' => $student->id,
                'classroom_id' => $classroom->id,
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'sequence_id' => $sequence->id,
                'value' => $value,
            ]);
        }

        return compact('classroom', 'sequence', 'studentA', 'studentB');
    }

    public function test_can_generate_student_bulletin_json(): void
    {
        $context = $this->seedBulletinContext();

        $response = $this->getJson('/api/v1/admin/students/'.$context['studentA']->id.'/bulletin?sequence_id='.$context['sequence']->id);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.summary.general_average', 14)
            ->assertJsonPath('data.summary.mention', 'Bien')
            ->assertJsonCount(2, 'data.subjects');
    }

    public function test_calculates_weighted_average_correctly(): void
    {
        $service = app(BulletinService::class);

        $average = $service->resolveMention(14);

        $this->assertSame('Bien', $average);
    }

    public function test_can_generate_classroom_bulletins_with_rankings(): void
    {
        $context = $this->seedBulletinContext();

        $response = $this->getJson('/api/v1/admin/classrooms/'.$context['classroom']->id.'/bulletins?sequence_id='.$context['sequence']->id);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data.bulletins')
            ->assertJsonPath('data.bulletins.0.summary.rank', 1)
            ->assertJsonPath('data.bulletins.0.summary.general_average', 14)
            ->assertJsonPath('data.bulletins.1.summary.rank', 2)
            ->assertJsonPath('data.class_average', 12.25);
    }

    public function test_returns_422_when_no_grades_for_sequence(): void
    {
        $context = $this->seedBulletinContext();
        $emptySequence = Sequence::query()->create([
            'number' => 2,
            'name' => '2ème séquence',
            'school_year' => '2025-2026',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/admin/students/'.$context['studentA']->id.'/bulletin?sequence_id='.$emptySequence->id);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_can_download_student_bulletin_pdf(): void
    {
        $context = $this->seedBulletinContext();

        $response = $this->get('/api/v1/admin/students/'.$context['studentA']->id.'/bulletin/pdf?sequence_id='.$context['sequence']->id);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
