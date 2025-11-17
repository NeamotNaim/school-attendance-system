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

            // Calculate summary
            $summary = [
                'present' => collect($recorded)->where('status', 'present')->count(),
                'absent' => collect($recorded)->where('status', 'absent')->count(),
                'late' => collect($recorded)->where('status', 'late')->count(),
                'total' => count($recorded),
            ];

            // Get class and section from first student
            $firstStudent = Student::find($students[0]['student_id'] ?? null);
            $class = $firstStudent->class ?? null;
            $section = $firstStudent->section ?? null;

            // Dispatch bulk attendance recorded event
            event(new \App\Events\AttendanceRecorded($recorded, $date, $class, $section, $summary));

            // Dispatch individual events for absent students
            foreach ($recorded as $attendance) {
                if ($attendance->status === 'absent') {
                    $student = Student::find($attendance->student_id);
                    if ($student) {
                        event(new \App\Events\StudentMarkedAbsent($student, $attendance, $date));
                    }
                }
            }

            return [
                'success' => true,
                'recorded' => count($recorded),
                'errors' => count($errors),
                'data' => $recorded,
                'error_details' => $errors,
                'summary' => $summary,
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
     * @param string|null $section
     * @return array
     */
    public function generateMonthlyReport(string $month, ?string $class = null, ?string $section = null): array
    {
        $cacheKey = "attendance_report_{$month}_" . ($class ?? 'all') . "_" . ($section ?? 'all');
        
        return Cache::remember($cacheKey, 3600, function () use ($month, $class, $section) {
            $startDate = Carbon::parse($month . '-01')->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            $query = Student::with(['attendances' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate])
                  ->select('id', 'student_id', 'date', 'status', 'note');
            }]);

            if ($class) {
                $query->where('class', $class);
            }

            if ($section) {
                $query->where('section', $section);
            }

            $students = $query->get();

            // Get total school days (days when attendance was recorded)
            $schoolDays = Attendance::whereBetween('date', [$startDate, $endDate])
                ->distinct('date')
                ->count('date');

            // If no attendance recorded yet, use current date as reference
            if ($schoolDays === 0) {
                $today = Carbon::today();
                if ($today->between($startDate, $endDate)) {
                    $schoolDays = $startDate->diffInDays($today) + 1;
                } else if ($today->greaterThan($endDate)) {
                    $schoolDays = $startDate->diffInDays($endDate) + 1;
                } else {
                    $schoolDays = 1; // Future month
                }
            }

            $report = [];
            foreach ($students as $student) {
                $presentDays = $student->attendances->where('status', 'present')->count();
                $absentDays = $student->attendances->where('status', 'absent')->count();
                $lateDays = $student->attendances->where('status', 'late')->count();
                $recordedDays = $presentDays + $absentDays + $lateDays;
                
                // Calculate attendance percentage based on recorded days
                $attendancePercentage = $recordedDays > 0 
                    ? ($presentDays / $recordedDays) * 100 
                    : 0;

                $report[] = [
                    'student_id' => $student->student_id,
                    'name' => $student->name,
                    'class' => $student->class,
                    'section' => $student->section,
                    'total_days' => $schoolDays,
                    'recorded_days' => $recordedDays,
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
                'section' => $section,
                'total_students' => count($report),
                'students' => $report,
                'summary' => [
                    'total_students' => count($report),
                    'total_present' => array_sum(array_column($report, 'present_days')),
                    'total_absent' => array_sum(array_column($report, 'absent_days')),
                    'total_late' => array_sum(array_column($report, 'late_days')),
                ],
            ];
        });
    }

    /**
     * Generate weekly attendance report.
     *
     * @param string $startDate Format: Y-m-d
     * @param string|null $class
     * @param string|null $section
     * @return array
     */
    public function generateWeeklyReport(string $startDate, ?string $class = null, ?string $section = null): array
    {
        $start = Carbon::parse($startDate);
        $end = $start->copy()->addDays(6); // 7 days total
        
        $cacheKey = "attendance_weekly_{$startDate}_" . ($class ?? 'all') . "_" . ($section ?? 'all');
        
        return Cache::remember($cacheKey, 1800, function () use ($start, $end, $class, $section) {
            $query = Student::with(['attendances' => function ($q) use ($start, $end) {
                $q->whereBetween('date', [$start, $end])
                  ->select('id', 'student_id', 'date', 'status', 'note');
            }]);

            if ($class) {
                $query->where('class', $class);
            }

            if ($section) {
                $query->where('section', $section);
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
                $attendancePercentage = $totalDays > 0 ? ($presentDays / $totalDays) * 100 : 0;

                // Update daily stats
                foreach ($student->attendances as $attendance) {
                    $dateStr = is_string($attendance->date) 
                        ? $attendance->date 
                        : $attendance->date->format('Y-m-d');
                    
                    if (isset($dailyStats[$dateStr])) {
                        $dailyStats[$dateStr]['total']++;
                        if (isset($dailyStats[$dateStr][$attendance->status])) {
                            $dailyStats[$dateStr][$attendance->status]++;
                        }
                    }
                }

                $report[] = [
                    'student_id' => $student->student_id,
                    'name' => $student->name,
                    'class' => $student->class,
                    'section' => $student->section,
                    'present_days' => $presentDays,
                    'absent_days' => $absentDays,
                    'late_days' => $lateDays,
                    'attendance_percentage' => round($attendancePercentage, 2),
                ];
            }

            // Calculate attendance percentage for daily stats
            foreach ($dailyStats as $dateStr => &$stats) {
                if ($stats['total'] > 0) {
                    $stats['attendance_percentage'] = round(($stats['present'] / $stats['total']) * 100, 2);
                } else {
                    $stats['attendance_percentage'] = 0;
                }
            }
            unset($stats); // Break reference

            return [
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'total_students' => count($students),
                'daily_stats' => array_values($dailyStats),
                'students' => $report,
                'summary' => [
                    'total_students' => count($students),
                    'average_present' => count($report) > 0 ? round(array_sum(array_column($report, 'present_days')) / count($report), 2) : 0,
                    'average_absent' => count($report) > 0 ? round(array_sum(array_column($report, 'absent_days')) / count($report), 2) : 0,
                    'average_late' => count($report) > 0 ? round(array_sum(array_column($report, 'late_days')) / count($report), 2) : 0,
                ],
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
            $present = Attendance::whereDate('date', $date)
                ->where('status', 'present')
                ->count();
            $absent = Attendance::whereDate('date', $date)
                ->where('status', 'absent')
                ->count();
            $late = Attendance::whereDate('date', $date)
                ->where('status', 'late')
                ->count();
            $recorded = Attendance::whereDate('date', $date)->distinct('student_id')->count();

            // Calculate attendance percentage based on present vs recorded
            $attendancePercentage = $recorded > 0 
                ? round(($present / $recorded) * 100, 2) 
                : 0;

            return [
                'date' => $date,
                'total_students' => $totalStudents,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'recorded' => $recorded,
                'not_recorded' => $totalStudents - $recorded,
                'attendance_percentage' => $attendancePercentage,
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

