<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\Attendance;
use App\Events\AttendanceRecorded;
use App\Events\StudentMarkedAbsent;
use App\Events\LowAttendanceDetected;
use App\Events\ReportGenerated;

class TestEvents extends Command
{
    protected $signature = 'events:test {event?}';
    protected $description = 'Test attendance events system';

    public function handle()
    {
        $event = $this->argument('event');

        if (!$event) {
            $event = $this->choice(
                'Which event would you like to test?',
                ['all', 'attendance-recorded', 'student-absent', 'low-attendance', 'report-generated'],
                0
            );
        }

        switch ($event) {
            case 'all':
                $this->testAllEvents();
                break;
            case 'attendance-recorded':
                $this->testAttendanceRecorded();
                break;
            case 'student-absent':
                $this->testStudentAbsent();
                break;
            case 'low-attendance':
                $this->testLowAttendance();
                break;
            case 'report-generated':
                $this->testReportGenerated();
                break;
            default:
                $this->error('Invalid event type');
                return 1;
        }

        $this->newLine();
        $this->info('✓ Events dispatched successfully!');
        $this->info('Check logs: tail -f storage/logs/laravel.log');
        
        return 0;
    }

    private function testAllEvents()
    {
        $this->info('Testing all events...');
        $this->newLine();
        
        $this->testAttendanceRecorded();
        $this->testStudentAbsent();
        $this->testLowAttendance();
        $this->testReportGenerated();
    }

    private function testAttendanceRecorded()
    {
        $this->line('→ Testing AttendanceRecorded event...');
        
        $attendances = Attendance::with('student')->take(5)->get();
        
        if ($attendances->isEmpty()) {
            $this->warn('  No attendance records found. Creating sample data...');
            // You could create sample data here if needed
            return;
        }

        $summary = [
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'total' => $attendances->count(),
        ];

        $firstStudent = $attendances->first()->student;
        
        event(new AttendanceRecorded(
            $attendances,
            now()->format('Y-m-d'),
            $firstStudent->class ?? '10',
            $firstStudent->section ?? 'A',
            $summary
        ));

        $this->info('  ✓ AttendanceRecorded event dispatched');
        $this->line("    - Date: " . now()->format('Y-m-d'));
        $this->line("    - Class: " . ($firstStudent->class ?? '10'));
        $this->line("    - Section: " . ($firstStudent->section ?? 'A'));
        $this->line("    - Present: {$summary['present']}, Absent: {$summary['absent']}, Late: {$summary['late']}");
    }

    private function testStudentAbsent()
    {
        $this->line('→ Testing StudentMarkedAbsent event...');
        
        $student = Student::first();
        
        if (!$student) {
            $this->warn('  No students found in database');
            return;
        }

        $attendance = Attendance::where('student_id', $student->id)
            ->where('status', 'absent')
            ->first();

        if (!$attendance) {
            // Create a sample absent record
            $attendance = new Attendance([
                'student_id' => $student->id,
                'date' => now()->format('Y-m-d'),
                'status' => 'absent',
            ]);
        }

        event(new StudentMarkedAbsent($student, $attendance, now()->format('Y-m-d')));

        $this->info('  ✓ StudentMarkedAbsent event dispatched');
        $this->line("    - Student: {$student->name} ({$student->student_id})");
        $this->line("    - Class: {$student->class} - Section: {$student->section}");
        $this->line("    - Date: " . now()->format('Y-m-d'));
    }

    private function testLowAttendance()
    {
        $this->line('→ Testing LowAttendanceDetected event...');
        
        $student = Student::first();
        
        if (!$student) {
            $this->warn('  No students found in database');
            return;
        }

        $percentage = 65.5;
        $threshold = 75;

        event(new LowAttendanceDetected($student, $percentage, $threshold, 'monthly'));

        $this->info('  ✓ LowAttendanceDetected event dispatched');
        $this->line("    - Student: {$student->name} ({$student->student_id})");
        $this->line("    - Attendance: {$percentage}%");
        $this->line("    - Threshold: {$threshold}%");
        $this->line("    - Period: monthly");
        $this->warn("    ⚠ Alert: Attendance below threshold!");
    }

    private function testReportGenerated()
    {
        $this->line('→ Testing ReportGenerated event...');
        
        $reportData = [
            ['student_id' => 'STU001', 'name' => 'Test Student', 'attendance' => 85],
            ['student_id' => 'STU002', 'name' => 'Another Student', 'attendance' => 90],
        ];

        event(new ReportGenerated(
            'monthly',
            $reportData,
            now()->format('Y-m'),
            '10',
            'A',
            storage_path('app/reports/test_report.csv')
        ));

        $this->info('  ✓ ReportGenerated event dispatched');
        $this->line("    - Type: monthly");
        $this->line("    - Period: " . now()->format('Y-m'));
        $this->line("    - Class: 10 - Section: A");
        $this->line("    - Records: " . count($reportData));
    }
}
