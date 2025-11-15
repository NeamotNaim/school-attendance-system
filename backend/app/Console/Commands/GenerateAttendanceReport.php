<?php

namespace App\Console\Commands;

use App\Services\AttendanceService;
use Illuminate\Console\Command;

class GenerateAttendanceReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:generate-report {month} {class?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly attendance report for a specific class';

    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        parent::__construct();
        $this->attendanceService = $attendanceService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $month = $this->argument('month');
        $class = $this->argument('class');

        // Validate month format
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $this->error('Invalid month format. Use YYYY-MM (e.g., 2024-01)');
            return 1;
        }

        $this->info("Generating attendance report for {$month}" . ($class ? " - Class: {$class}" : " - All Classes"));

        try {
            $report = $this->attendanceService->generateMonthlyReport($month, $class);

            $this->info("\nReport Summary:");
            $this->info("Month: {$report['month']}");
            $this->info("Class: " . ($report['class'] ?? 'All'));
            $this->info("Total Students: {$report['total_students']}");

            $this->table(
                ['Student ID', 'Name', 'Class', 'Section', 'Present', 'Absent', 'Late', 'Percentage'],
                array_map(function ($student) {
                    return [
                        $student['student_id'],
                        $student['name'],
                        $student['class'],
                        $student['section'],
                        $student['present_days'],
                        $student['absent_days'],
                        $student['late_days'],
                        number_format($student['attendance_percentage'], 2) . '%',
                    ];
                }, $report['students'])
            );

            $this->info("\nReport generated successfully!");
            return 0;
        } catch (\Exception $e) {
            $this->error("Error generating report: " . $e->getMessage());
            return 1;
        }
    }
}
