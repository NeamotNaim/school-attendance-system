<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AttendanceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AttendanceService();
    }

    public function test_can_record_bulk_attendance(): void
    {
        $students = Student::factory()->count(3)->create();
        $user = User::factory()->create();

        $attendanceData = [
            'date' => now()->format('Y-m-d'),
            'students' => $students->map(function ($student) {
                return [
                    'student_id' => $student->id,
                    'status' => 'present',
                ];
            })->toArray(),
        ];

        $result = $this->service->recordBulkAttendance($attendanceData, $user->id);

        $this->assertTrue($result['success']);
        $this->assertEquals(3, $result['recorded']);
        $this->assertEquals(3, Attendance::count());
    }

    public function test_can_generate_monthly_report(): void
    {
        $student = Student::factory()->create();
        $user = User::factory()->create();
        $month = now()->format('Y-m');

        // Create some attendance records
        Attendance::factory()->count(5)->create([
            'student_id' => $student->id,
            'date' => now()->startOfMonth()->addDays(rand(0, 20)),
            'status' => 'present',
            'recorded_by' => $user->id,
        ]);

        $report = $this->service->generateMonthlyReport($month);

        $this->assertArrayHasKey('month', $report);
        $this->assertArrayHasKey('total_students', $report);
        $this->assertArrayHasKey('students', $report);
        $this->assertGreaterThan(0, count($report['students']));
    }

    public function test_attendance_statistics_are_cached(): void
    {
        Cache::flush();
        $date = now()->format('Y-m-d');

        $stats1 = $this->service->getAttendanceStatistics($date);
        $this->assertTrue(Cache::has("attendance_stats_{$date}"));

        $stats2 = $this->service->getAttendanceStatistics($date);
        $this->assertEquals($stats1, $stats2);
    }
}
