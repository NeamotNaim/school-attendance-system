<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Holiday;
use Carbon\Carbon;

class GenerateAttendanceReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:generate-report 
                            {month : The month in YYYY-MM format} 
                            {class : The class name} 
                            {--section= : Optional section name}
                            {--format=table : Export format (csv, json, table)}
                            {--output= : Output file path (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly attendance report for a specific class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $month = $this->argument('month');
        $class = $this->argument('class');
        $section = $this->option('section');
        $format = $this->option('format');
        $outputPath = $this->option('output');

        // Validate month format
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $this->error('Invalid month format. Use YYYY-MM (e.g., 2024-11)');
            return 1;
        }

        $this->info("Generating attendance report for Class {$class}" . ($section ? " Section {$section}" : '') . " - {$month}");

        // Get students
        $studentsQuery = Student::where('class', $class);
        if ($section) {
            $studentsQuery->where('section', $section);
        }
        $students = $studentsQuery->get();

        if ($students->isEmpty()) {
            $this->error('No students found for the specified class' . ($section ? ' and section' : ''));
            return 1;
        }

        $this->info("Found {$students->count()} students");

        // Calculate date range
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = Carbon::parse($month . '-01')->endOfMonth();

        // Get holidays
        $holidays = Holiday::whereBetween('date', [$startDate, $endDate])
            ->pluck('date')
            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
            ->toArray();

        // Calculate school days
        $schoolDays = 0;
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            if (!$currentDate->isWeekend() && !in_array($currentDate->format('Y-m-d'), $holidays)) {
                $schoolDays++;
            }
            $currentDate->addDay();
        }

        $this->info("School days in {$month}: {$schoolDays}");

        // Generate report data
        $reportData = [];
        $progressBar = $this->output->createProgressBar($students->count());
        $progressBar->start();

        foreach ($students as $student) {
            $attendances = Attendance::where('student_id', $student->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $recordedDays = $attendances->count();
            $presentDays = $attendances->where('status', 'present')->count();
            $absentDays = $attendances->where('status', 'absent')->count();
            $lateDays = $attendances->where('status', 'late')->count();

            $attendancePercentage = $recordedDays > 0 
                ? round(($presentDays / $recordedDays) * 100, 2) 
                : 0;

            $reportData[] = [
                'student_id' => $student->student_id,
                'name' => $student->name,
                'class' => $student->class,
                'section' => $student->section,
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'late_days' => $lateDays,
                'recorded_days' => $recordedDays,
                'school_days' => $schoolDays,
                'attendance_percentage' => $attendancePercentage,
            ];

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $filePath = null;

        // Export based on format
        switch ($format) {
            case 'csv':
                $filePath = $this->exportToCsv($reportData, $month, $class, $section, $outputPath);
                break;
            case 'json':
                $filePath = $this->exportToJson($reportData, $month, $class, $section, $outputPath);
                break;
            case 'table':
                $this->displayTable($reportData);
                break;
            default:
                $this->error("Invalid format: {$format}. Use csv, json, or table");
                return 1;
        }

        // Dispatch report generated event
        event(new \App\Events\ReportGenerated('monthly', $reportData, $month, $class, $section, $filePath));

        $this->info('Report generated successfully!');
        return 0;
    }

    /**
     * Export report to CSV
     */
    private function exportToCsv($data, $month, $class, $section, $outputPath = null)
    {
        $filename = $outputPath ?? storage_path("app/reports/attendance_{$month}_class_{$class}" . ($section ? "_section_{$section}" : '') . ".csv");
        
        // Ensure directory exists
        $directory = dirname($filename);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = fopen($filename, 'w');

        // Write header
        fputcsv($file, [
            'Student ID',
            'Name',
            'Class',
            'Section',
            'Present Days',
            'Absent Days',
            'Late Days',
            'Recorded Days',
            'School Days',
            'Attendance %'
        ]);

        // Write data
        foreach ($data as $row) {
            fputcsv($file, [
                $row['student_id'],
                $row['name'],
                $row['class'],
                $row['section'],
                $row['present_days'],
                $row['absent_days'],
                $row['late_days'],
                $row['recorded_days'],
                $row['school_days'],
                $row['attendance_percentage'] . '%'
            ]);
        }

        fclose($file);

        $this->info("CSV report saved to: {$filename}");
        
        return $filename;
    }

    /**
     * Export report to JSON
     */
    private function exportToJson($data, $month, $class, $section, $outputPath = null)
    {
        $filename = $outputPath ?? storage_path("app/reports/attendance_{$month}_class_{$class}" . ($section ? "_section_{$section}" : '') . ".json");
        
        // Ensure directory exists
        $directory = dirname($filename);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $report = [
            'month' => $month,
            'class' => $class,
            'section' => $section,
            'generated_at' => now()->toIso8601String(),
            'total_students' => count($data),
            'students' => $data
        ];

        file_put_contents($filename, json_encode($report, JSON_PRETTY_PRINT));

        $this->info("JSON report saved to: {$filename}");
        
        return $filename;
    }

    /**
     * Display report as table in console
     */
    private function displayTable($data)
    {
        $this->table(
            ['Student ID', 'Name', 'Class', 'Section', 'Present', 'Absent', 'Late', 'Recorded', 'School Days', 'Attendance %'],
            array_map(function ($row) {
                return [
                    $row['student_id'],
                    $row['name'],
                    $row['class'],
                    $row['section'],
                    $row['present_days'],
                    $row['absent_days'],
                    $row['late_days'],
                    $row['recorded_days'],
                    $row['school_days'],
                    $row['attendance_percentage'] . '%'
                ];
            }, $data)
        );

        // Summary statistics
        $totalPresent = array_sum(array_column($data, 'present_days'));
        $totalAbsent = array_sum(array_column($data, 'absent_days'));
        $totalLate = array_sum(array_column($data, 'late_days'));
        $avgAttendance = round(array_sum(array_column($data, 'attendance_percentage')) / count($data), 2);

        $this->newLine();
        $this->info('Summary Statistics:');
        $this->line("Total Students: " . count($data));
        $this->line("Total Present Days: {$totalPresent}");
        $this->line("Total Absent Days: {$totalAbsent}");
        $this->line("Total Late Days: {$totalLate}");
        $this->line("Average Attendance: {$avgAttendance}%");
    }
}
