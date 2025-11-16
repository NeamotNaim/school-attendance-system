<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Get weekly attendance report.
     */
    public function weekly(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $class = $request->get('class');
        $section = $request->get('section');

        $report = $this->attendanceService->generateWeeklyReport($startDate, $class, $section);

        return response()->json([
            'data' => $report,
        ]);
    }

    /**
     * Get monthly attendance report.
     */
    public function monthly(Request $request): JsonResponse
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $class = $request->get('class');
        $section = $request->get('section');

        $report = $this->attendanceService->generateMonthlyReport($month, $class, $section);

        return response()->json([
            'data' => $report,
        ]);
    }

    /**
     * Get class-wise attendance comparison.
     */
    public function classComparison(Request $request): JsonResponse
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        
        $classes = \App\Models\SchoolClass::where('is_active', true)->orderBy('name')->get();
        $comparison = [];

        foreach ($classes as $class) {
            // Use class name instead of ID
            $report = $this->attendanceService->generateMonthlyReport($month, $class->name);
            
            $totalStudents = count($report['students']);
            $totalPresent = array_sum(array_column($report['students'], 'present_days'));
            $totalAbsent = array_sum(array_column($report['students'], 'absent_days'));
            $totalDays = $totalStudents > 0 ? $report['students'][0]['total_days'] ?? 0 : 0;
            $avgPercentage = $totalStudents > 0 
                ? array_sum(array_column($report['students'], 'attendance_percentage')) / $totalStudents
                : 0;

            $comparison[] = [
                'class_id' => $class->id,
                'class_name' => $class->name,
                'total_students' => $totalStudents,
                'total_present' => $totalPresent,
                'total_absent' => $totalAbsent,
                'total_days' => $totalDays,
                'average_attendance_percentage' => round($avgPercentage, 2),
            ];
        }

        return response()->json([
            'month' => $month,
            'data' => $comparison,
        ]);
    }

    /**
     * Get low attendance students.
     */
    public function lowAttendance(Request $request): JsonResponse
    {
        $threshold = (float) $request->get('threshold', 75.0);
        $month = $request->get('month', Carbon::now()->format('Y-m'));

        $lowAttendance = $this->attendanceService->getLowAttendanceStudents($threshold, $month);

        return response()->json([
            'data' => $lowAttendance,
        ]);
    }

    /**
     * Get attendance trends.
     */
    public function trends(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->subMonths(6)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $classId = $request->get('class_id');

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        $trends = [];
        $current = $start->copy()->startOfMonth();

        while ($current <= $end) {
            $month = $current->format('Y-m');
            $report = $this->attendanceService->generateMonthlyReport($month, $classId);
            
            $totalStudents = count($report['students']);
            $avgPercentage = $totalStudents > 0
                ? array_sum(array_column($report['students'], 'attendance_percentage')) / $totalStudents
                : 0;

            $trends[] = [
                'month' => $month,
                'total_students' => $totalStudents,
                'average_attendance_percentage' => round($avgPercentage, 2),
            ];

            $current->addMonth();
        }

        return response()->json([
            'data' => $trends,
        ]);
    }

    /**
     * Export monthly report to CSV.
     */
    public function exportMonthly(Request $request): JsonResponse
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $classId = $request->get('class_id');

        $exportService = new \App\Services\Export\ExportService($this->attendanceService);
        $filePath = $exportService->exportMonthlyReport($month, $classId);
        $url = $exportService->getExportUrl($filePath);

        return response()->json([
            'message' => 'Report exported successfully',
            'file_path' => $filePath,
            'download_url' => $url,
        ]);
    }

    /**
     * Export daily attendance to CSV.
     */
    public function exportDaily(Request $request): JsonResponse
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $classId = $request->get('class_id');
        $sectionId = $request->get('section_id');

        $exportService = new \App\Services\Export\ExportService($this->attendanceService);
        $filePath = $exportService->exportDailyAttendance($date, $classId, $sectionId);
        $url = $exportService->getExportUrl($filePath);

        return response()->json([
            'message' => 'Daily attendance exported successfully',
            'file_path' => $filePath,
            'download_url' => $url,
        ]);
    }

    /**
     * Export student history to CSV.
     */
    public function exportStudentHistory(Request $request, string $studentId): JsonResponse
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $exportService = new \App\Services\Export\ExportService($this->attendanceService);
        $filePath = $exportService->exportStudentHistory((int) $studentId, $startDate, $endDate);
        $url = $exportService->getExportUrl($filePath);

        return response()->json([
            'message' => 'Student history exported successfully',
            'file_path' => $filePath,
            'download_url' => $url,
        ]);
    }
}
