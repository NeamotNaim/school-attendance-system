<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    public function test_can_create_student(): void
    {
        $response = $this->postJson('/api/students', [
            'student_id' => 'STU001',
            'name' => 'John Doe',
            'class' => '10',
            'section' => 'A',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'student_id',
                    'name',
                    'class',
                    'section',
                ],
            ]);

        $this->assertDatabaseHas('students', [
            'student_id' => 'STU001',
            'name' => 'John Doe',
        ]);
    }

    public function test_can_list_students(): void
    {
        Student::factory()->count(5)->create();

        $response = $this->getJson('/api/students');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'student_id', 'name', 'class', 'section'],
                ],
                'meta',
            ]);
    }

    public function test_can_update_student(): void
    {
        $student = Student::factory()->create([
            'student_id' => 'STU001',
            'name' => 'John Doe',
        ]);

        $response = $this->putJson("/api/students/{$student->id}", [
            'name' => 'Jane Doe',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'Jane Doe',
        ]);
    }

    public function test_can_delete_student(): void
    {
        $student = Student::factory()->create();

        $response = $this->deleteJson("/api/students/{$student->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('students', [
            'id' => $student->id,
        ]);
    }

    public function test_student_validation_required_fields(): void
    {
        $response = $this->postJson('/api/students', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['student_id', 'name', 'class', 'section']);
    }
}
