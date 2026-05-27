<?php

namespace Tests\Feature\Admin;

use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassroomApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_classrooms(): void
    {
        Classroom::query()->create([
            'name' => 'Terminale C',
            'specialty' => 'Sciences',
            'level' => 'Terminale',
        ]);

        $response = $this->getJson('/api/v1/admin/classrooms');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_create_classroom(): void
    {
        $response = $this->postJson('/api/v1/admin/classrooms', [
            'name' => 'Première D',
            'specialty' => 'Sciences',
            'level' => 'Première',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Première D');

        $this->assertDatabaseHas('classrooms', [
            'name' => 'Première D',
            'specialty' => 'Sciences',
            'level' => 'Première',
        ]);
    }

    public function test_cannot_create_duplicate_classroom(): void
    {
        Classroom::query()->create([
            'name' => 'Terminale C',
            'specialty' => 'Sciences',
            'level' => 'Terminale',
        ]);

        $response = $this->postJson('/api/v1/admin/classrooms', [
            'name' => 'Terminale C',
            'specialty' => 'Sciences',
            'level' => 'Terminale',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_can_update_classroom(): void
    {
        $classroom = Classroom::query()->create([
            'name' => 'Terminale C',
            'specialty' => 'Sciences',
            'level' => 'Terminale',
        ]);

        $response = $this->putJson("/api/v1/admin/classrooms/{$classroom->id}", [
            'name' => 'Terminale C1',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Terminale C1');
    }

    public function test_can_delete_empty_classroom(): void
    {
        $classroom = Classroom::query()->create([
            'name' => 'Terminale C',
            'specialty' => 'Sciences',
            'level' => 'Terminale',
        ]);

        $response = $this->deleteJson("/api/v1/admin/classrooms/{$classroom->id}");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('classrooms', ['id' => $classroom->id]);
    }

    public function test_cannot_delete_classroom_with_students(): void
    {
        $classroom = Classroom::query()->create([
            'name' => 'Terminale C',
            'specialty' => 'Sciences',
            'level' => 'Terminale',
        ]);

        Student::query()->create([
            'matricule' => 'ESM-2026-001',
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'birth_date' => '2008-05-15',
            'classroom_id' => $classroom->id,
        ]);

        $response = $this->deleteJson("/api/v1/admin/classrooms/{$classroom->id}");

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_returns_404_for_unknown_classroom(): void
    {
        $response = $this->getJson('/api/v1/admin/classrooms/999');

        $response->assertNotFound();
    }
}
