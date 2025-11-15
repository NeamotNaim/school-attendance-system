<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Record bulk attendance for multiple students.
     *
     * @param array $attendanceData
     * @param int $recordedBy
     * @return array
     */
    public function recordBulkAttendance(array $attendanceData, int $recordedBy): array
    {
        $date = $attendanceData['date'] ?? Carbon::today()->format('Y-m-d');
        $students = $attendanceData['students'] ?? [];
        
        $recorded = [];
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($students as $studentData) {
                try {
                    $attendance = Attendance::updateOrCreate(
                        [
                            'student_id' => $studentData['student_id'],
                            'date' => $date,
                        ],
                        [
                            'status' => $studentData['status'] ?? 'present',
                            'note' => $studentData['note'] ?? null,
                            'recorded_by' => $recordedBy,
                        ]
                    );

                    $recorded[] = $attendance;
                } catch (\Exception $e) {
                    $errors[] = [
                        'student_id' => $studentData['student_id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];
                    Log::error('Failed to record attendance', [
                        'student_id' => $studentData['student_id'] ?? null,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            // Clear cache after recording
            $this->clearAttendanceCache($date);

            // Dispatch event for each recorded attendance
            foreach ($recorded as $attendance) {
                event(new \App\Events\AttendanceRecorded($attendance));
            }

            return [
                'success' => true,
                'recorded' => count($recorded),
                'errors' => count($errors),
                'data' => $recorded,
                'error_details' => $errors,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk attendance recording failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate monthly attendance report with eager loading.
     *
     * @param string $month Format: Y-m (e.g., 2024-01)
     * @param string|null $class
     * @return array
     */
    public function generateMonthlyReport(string $month, ?string $class = null): array
    {
        $cacheKey = "attendance_report_{$month}_" . ($class ?? 'all');
        
        return Cache::remember($cacheKey, 3600, function () use ($month, $class) {
            $startDate = Carbon::parse($month . '-01')->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            $query = Student::with(['attendances' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate])
                  ->select('id', 'student_id', 'date', 'status', 'note');
            }]);

            if ($class) {
                $query->where('class', $class);
            }

            $students = $query->get();

            $report = [];
            foreach ($students as $student) {
                $totalDays = $startDate->diffInDays($endDate) + 1;
                $presentDays = $student->attendances->where('status', 'present')->count();
                $absentDays = $student->attendances->where('status', 'absent')->count();
                $lateDays = $student->attendances->where('status', 'late')->count();
                $attendancePercentage = $totalDays > 0 ? ($presentDays / $totalDays) * 100 : 0;

                $report[] = [
                    'student_id' => $student->student_id,
                    'name' => $student->name,
                    'class' => $student->class,
                    'section' => $student->section,
                    'total_days' => $totalDays,
                    'present_days' => $presentDays,
                    'absent_days' => $absentDays,
                    'late_days' => $lateDays,
                    'attendance_percentage' => round($attendancePercentage, 2),
                    'attendances' => $student->attendances,
                ];
            }

            return [
                'month' => $month,
                'class' => $class,
                'total_students' => count($report),
                'students' => $report,
            ];
        });
    }

    /**
     * Generate weekly attendance report.
     *
     * @param string $startDate Format: Y-m-d
     * @param string|null $classId
     * @param string|null $sectionId
     * @return array
     */
    public function generateWeeklyReport(string $startDate, ?string $classId = null, ?string $sectionId = null): array
    {
        $start = Carbon::parse($startDate);
        $end = $start->copy()->addDays(6); // 7 days total
        
        $cacheKey = "attendance_weekly_{$startDate}_" . ($classId ?? 'all') . "_" . ($sectionId ?? 'all');
        
        return Cache::remember($cacheKey, 1800, function () use ($start, $end, $classId, $sectionId) {
            $query = Student::with(['attendances' => function ($q) use ($start, $end) {
                $q->whereBetween('date', [$start, $end])
                  ->select('id', 'student_id', 'date', 'status', 'note');
            }]);

            if ($classId) {
                $query->where('class_id', $classId);
            }

            if ($sectionId) {
                $query->where('section_id', $sectionId);
            }

            $students = $query->get();

            $dailyStats = [];
            $currentDate = $start->copy();
            while ($currentDate <= $end) {
                $dateStr = $currentDate->format('Y-m-d');
                $dailyStats[$dateStr] = [
                    'date' => $dateStr,
                    'present' => 0,
                    'absent' => 0,
                    'late' => 0,
                    'total' => 0,
                ];
                $currentDate->addDay();
            }

            $report = [];
            foreach ($students as $student) {
                $presentDays = $student->attendances->where('status', 'present')->count();
                $absentDays = $student->attendances->where('status', 'absent')->count();
                $lateDays = $student->attendances->where('status', 'late')->count();
                $totalDays = 7;
                $attendancePercentage = ($presentDays / $totalDays) * 100;

                // Update daily stats
                foreach ($student->attendances as $attendance) {
                    $dateStr = $attendance->date->format('Y-m-d');
                    if (isset($dailyStats[$dateStr])) {
                        $dailyStats[$dateStr]['total']++;
                        $dailyStats[$dateStr][$attendance->status]++;
                    }
                }

                $report[] = [
                    'student_id' => $student->student_id,
                    'name' => $student->name,
                    'class' => $student->className,
                    'section' => $student->sectionName,
                    'present_days' => $presentDays,
                    'absent_days' => $absentDays,
                    'late_days' => $lateDays,
                    'attendance_percentage' => round($attendancePercentage, 2),
                ];
            }

            return [
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'total_students' => count($report),
                'daily_statistics' => array_values($dailyStats),
                'students' => $report,
            ];
        });
    }

    /**
     * Get students with low attendance.
     *
     * @param float $threshold Minimum attendance percentage
     * @param string|null $month
     * @param bool $triggerNotifications Whether to trigger notification events
     * @return array
     */
    public function getLowAttendanceStudents(float $threshold = 75.0, ?string $month = null, bool $triggerNotifications = false): array
    {
        $month = $month ?? Carbon::now()->format('Y-m');
        $report = $this->generateMonthlyReport($month);
        
        $lowAttendance = array_filter($report['students'], function ($student) use ($threshold) {
            return $student['attendance_percentage'] < $threshold;
        });

        // Trigger notifications if requested
        if ($triggerNotifications) {
            foreach ($lowAttendance as $studentData) {
                $student = Student::where('student_id', $studentData['student_id'])->first();
                if ($student) {
                    event(new \App\Events\LowAttendanceDetected(
                        $student,
                        $studentData['attendance_percentage'],
                        $threshold,
                        $month
                    ));
                }
            }
        }

        return [
            'month' => $month,
            'threshold' => $threshold,
            'count' => count($lowAttendance),
            'students' => array_values($lowAttendance),
        ];
    }

    /**
     * Get attendance statistics with caching.
     *
     * @param string|null $date
     * @return array
     */
    public function getAttendanceStatistics(?string $date = null): array
    {
        $date = $date ?? Carbon::today()->format('Y-m-d');
        $cacheKey = "attendance_stats_{$date}";

        return Cache::remember($cacheKey, 1800, function () use ($date) {
            $totalStudents = Student::count();
            $present = Attendance::where('date', $date)
                ->where('status', 'present')
                ->count();
            $absent = Attendance::where('date', $date)
                ->where('status', 'absent')
                ->count();
            $late = Attendance::where('date', $date)
                ->where('status', 'late')
                ->count();
            $recorded = Attendance::where('date', $date)->distinct('student_id')->count();

            return [
                'date' => $date,
                'total_students' => $totalStudents,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'recorded' => $recorded,
                'not_recorded' => $totalStudents - $recorded,
                'attendance_percentage' => $totalStudents > 0 
                    ? round(($recorded / $totalStudents) * 100, 2) 
                    : 0,
            ];
        });
    }

    /**
     * Clear attendance cache for a specific date.
     *
     * @param string $date
     * @return void
     */
    public function clearAttendanceCache(string $date): void
    {
        $month = Carbon::parse($date)->format('Y-m');
        Cache::forget("attendance_stats_{$date}");
        Cache::forget("attendance_report_{$month}_all");
        
        // Clear class-specific caches
        $classes = Student::distinct('class')->pluck('class');
        foreach ($classes as $class) {
            Cache::forget("attendance_report_{$month}_{$class}");
        }
    }
}

