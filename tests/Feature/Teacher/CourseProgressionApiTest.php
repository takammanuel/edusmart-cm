<?php

namespace Tests\Feature\Teacher;

use App\Models\Classroom;
use App\Models\CourseProgression;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseProgressionApiTest extends TestCase
{
    use RefreshDatabase;

    private function seedProgressionContext(): array
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
            'email' => 'marie@example.cm',
            'main_subject_id' => $subject->id,
        ]);

        $teacher->classrooms()->attach($classroom->id, ['subject_id' => $subject->id]);

        return compact('classroom', 'subject', 'teacher');
    }

    public function test_can_list_course_progressions(): void
    {
        $context = $this->seedProgressionContext();

        CourseProgression::query()->create([
            'teacher_id' => $context['teacher']->id,
            'classroom_id' => $context['classroom']->id,
            'subject_id' => $context['subject']->id,
            'date' => now()->subDays(1),
            'content' => 'Chapitre 1: Calcul intégral',
        ]);

        $response = $this->getJson('/api/v1/teacher/course-progressions?teacher_id='.$context['teacher']->id.'&classroom_id='.$context['classroom']->id.'&subject_id='.$context['subject']->id);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_create_course_progression(): void
    {
        $context = $this->seedProgressionContext();

        $response = $this->postJson('/api/v1/teacher/course-progressions', [
            'teacher_id' => $context['teacher']->id,
            'classroom_id' => $context['classroom']->id,
            'subject_id' => $context['subject']->id,
            'date' => now()->format('Y-m-d'),
            'content' => 'Introduction aux dérivées',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.content', 'Introduction aux dérivées');

        $this->assertDatabaseHas('course_progressions', [
            'content' => 'Introduction aux dérivées',
        ]);
    }

    public function test_can_create_progression_bulk(): void
    {
        $context = $this->seedProgressionContext();

        $response = $this->postJson('/api/v1/teacher/course-progressions/bulk', [
            'teacher_id' => $context['teacher']->id,
            'classroom_id' => $context['classroom']->id,
            'subject_id' => $context['subject']->id,
            'progressions' => [
                [
                    'date' => now()->subDays(2)->format('Y-m-d'),
                    'content' => 'Chapitre 1: Calcul',
                ],
                [
                    'date' => now()->subDays(1)->format('Y-m-d'),
                    'content' => 'Chapitre 2: Analyse',
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');

        $this->assertDatabaseCount('course_progressions', 2);
    }

    public function test_can_show_course_progression(): void
    {
        $context = $this->seedProgressionContext();

        $progression = CourseProgression::query()->create([
            'teacher_id' => $context['teacher']->id,
            'classroom_id' => $context['classroom']->id,
            'subject_id' => $context['subject']->id,
            'date' => now()->format('Y-m-d'),
            'content' => 'Test content',
        ]);

        $response = $this->getJson('/api/v1/teacher/course-progressions/'.$progression->id);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.content', 'Test content');
    }

    public function test_can_update_course_progression(): void
    {
        $context = $this->seedProgressionContext();

        $progression = CourseProgression::query()->create([
            'teacher_id' => $context['teacher']->id,
            'classroom_id' => $context['classroom']->id,
            'subject_id' => $context['subject']->id,
            'date' => now()->format('Y-m-d'),
            'content' => 'Original content',
        ]);

        $response = $this->putJson('/api/v1/teacher/course-progressions/'.$progression->id, [
            'content' => 'Updated content',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.content', 'Updated content');
    }

    public function test_can_delete_course_progression(): void
    {
        $context = $this->seedProgressionContext();

        $progression = CourseProgression::query()->create([
            'teacher_id' => $context['teacher']->id,
            'classroom_id' => $context['classroom']->id,
            'subject_id' => $context['subject']->id,
            'date' => now()->format('Y-m-d'),
            'content' => 'To delete',
        ]);

        $response = $this->deleteJson('/api/v1/teacher/course-progressions/'.$progression->id);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('course_progressions', ['id' => $progression->id]);
    }

    public function test_cannot_create_for_unassigned_teacher(): void
    {
        $context = $this->seedProgressionContext();

        $otherTeacher = Teacher::query()->create([
            'first_name' => 'Paul',
            'last_name' => 'Nkomo',
            'email' => 'paul@example.cm',
            'main_subject_id' => $context['subject']->id,
        ]);

        $response = $this->postJson('/api/v1/teacher/course-progressions', [
            'teacher_id' => $otherTeacher->id,
            'classroom_id' => $context['classroom']->id,
            'subject_id' => $context['subject']->id,
            'date' => now()->format('Y-m-d'),
            'content' => 'Test',
        ]);

        $response->assertUnprocessable();
    }

    public function test_can_filter_by_date_range(): void
    {
        $context = $this->seedProgressionContext();

        CourseProgression::query()->create([
            'teacher_id' => $context['teacher']->id,
            'classroom_id' => $context['classroom']->id,
            'subject_id' => $context['subject']->id,
            'date' => now()->subDays(5)->format('Y-m-d'),
            'content' => 'Old',
        ]);

        CourseProgression::query()->create([
            'teacher_id' => $context['teacher']->id,
            'classroom_id' => $context['classroom']->id,
            'subject_id' => $context['subject']->id,
            'date' => now()->format('Y-m-d'),
            'content' => 'Recent',
        ]);

        $response = $this->getJson('/api/v1/teacher/course-progressions?teacher_id='.$context['teacher']->id.'&classroom_id='.$context['classroom']->id.'&subject_id='.$context['subject']->id.'&date_from='.now()->subDays(2)->format('Y-m-d'));

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
