<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    public function test_can_record_bulk_attendance(): void
    {
        $students = Student::factory()->count(3)->create();
        $user = User::first();

        $attendanceData = [
            'date' => now()->format('Y-m-d'),
            'students' => $students->map(function ($student) {
                return [
                    'student_id' => $student->id,
                    'status' => 'present',
                ];
            })->toArray(),
        ];

        $response = $this->postJson('/api/attendances/bulk', $attendanceData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'success',
                    'recorded',
                ],
            ]);

        $this->assertEquals(3, Attendance::count());
    }

    public function test_can_get_attendance_statistics(): void
    {
        $student = Student::factory()->create();
        $user = User::first();

        Attendance::factory()->create([
            'student_id' => $student->id,
            'date' => now(),
            'status' => 'present',
            'recorded_by' => $user->id,
        ]);

        $response = $this->getJson('/api/attendances/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'date',
                    'total_students',
                    'present',
                    'absent',
                    'late',
                ],
            ]);
    }

    public function test_can_get_monthly_report(): void
    {
        $student = Student::factory()->create();
        $user = User::first();
        $month = now()->format('Y-m');

        Attendance::factory()->count(5)->create([
            'student_id' => $student->id,
            'date' => now()->startOfMonth()->addDays(rand(0, 20)),
            'status' => 'present',
            'recorded_by' => $user->id,
        ]);

        $response = $this->getJson("/api/attendances/report?month={$month}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'month',
                    'total_students',
                    'students' => [
                        '*' => [
                            'student_id',
                            'name',
                            'present_days',
                            'absent_days',
                            'attendance_percentage',
                        ],
                    ],
                ],
            ]);
    }
}
