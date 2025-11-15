<?php

namespace App\Services\Export;

use App\Models\Attendance;
use App\Models\Student;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ExportService
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Export attendance to CSV format.
     *
     * @param array $data
     * @param string $filename
     * @return string Path to exported file
     */
    public function exportToCsv(array $data, string $filename): string
    {
        $filePath = 'exports/' . $filename . '.csv';
        $fullPath = storage_path('app/public/' . $filePath);
        
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = fopen($fullPath, 'w');

        // Write headers
        if (!empty($data)) {
            fputcsv($file, array_keys($data[0]));
        }

        // Write data
        foreach ($data as $row) {
            fputcsv($file, $row);
        }

        fclose($file);

        return $filePath;
    }

    /**
     * Export monthly report to CSV.
     *
     * @param string $month
     * @param string|null $classId
     * @return string
     */
    public function exportMonthlyReport(string $month, ?string $classId = null): string
    {
        $report = $this->attendanceService->generateMonthlyReport($month, $classId);
        
        $data = [];
        foreach ($report['students'] as $student) {
            $data[] = [
                'Student ID' => $student['student_id'],
                'Name' => $student['name'],
                'Class' => $student['class'],
                'Section' => $student['section'],
                'Total Days' => $student['total_days'],
                'Present Days' => $student['present_days'],
                'Absent Days' => $student['absent_days'],
                'Late Days' => $student['late_days'],
                'Attendance Percentage' => $student['attendance_percentage'] . '%',
            ];
        }

        $filename = 'monthly_report_' . $month . ($classId ? '_class_' . $classId : '');
        return $this->exportToCsv($data, $filename);
    }

    /**
     * Export daily attendance to CSV.
     *
     * @param string $date
     * @param string|null $classId
     * @param string|null $sectionId
     * @return string
     */
    public function exportDailyAttendance(string $date, ?string $classId = null, ?string $sectionId = null): string
    {
        $query = Attendance::whereDate('date', $date)
            ->with(['student.schoolClass', 'student.section']);

        if ($classId) {
            $query->whereHas('student', fn($q) => $q->where('class_id', $classId));
        }

        if ($sectionId) {
            $query->whereHas('student', fn($q) => $q->where('section_id', $sectionId));
        }

        $attendances = $query->get();

        $data = [];
        foreach ($attendances as $attendance) {
            $data[] = [
                'Date' => $attendance->date->format('Y-m-d'),
                'Student ID' => $attendance->student->student_id,
                'Student Name' => $attendance->student->name,
                'Class' => $attendance->student->className,
                'Section' => $attendance->student->sectionName,
                'Status' => ucfirst($attendance->status),
                'Note' => $attendance->note ?? '',
            ];
        }

        $filename = 'daily_attendance_' . $date . ($classId ? '_class_' . $classId : '');
        return $this->exportToCsv($data, $filename);
    }

    /**
     * Export student attendance history to CSV.
     *
     * @param int $studentId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return string
     */
    public function exportStudentHistory(int $studentId, ?string $startDate = null, ?string $endDate = null): string
    {
        $student = Student::findOrFail($studentId);
        
        $query = $student->attendances()->orderBy('date', 'desc');

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        $attendances = $query->get();

        $data = [];
        foreach ($attendances as $attendance) {
            $data[] = [
                'Date' => $attendance->date->format('Y-m-d'),
                'Status' => ucfirst($attendance->status),
                'Note' => $attendance->note ?? '',
                'Recorded By' => $attendance->recorder->name ?? 'N/A',
            ];
        }

        $filename = 'student_history_' . $student->student_id . '_' . ($startDate ?? 'all');
        return $this->exportToCsv($data, $filename);
    }

    /**
     * Get export file URL.
     *
     * @param string $filePath
     * @return string
     */
    public function getExportUrl(string $filePath): string
    {
        return asset('storage/' . $filePath);
    }
}

