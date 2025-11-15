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

