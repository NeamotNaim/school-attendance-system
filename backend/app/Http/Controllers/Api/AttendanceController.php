<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Attendance::with(['student', 'recorder']);

        // Filter by date
        if ($request->has('date')) {
            $query->where('date', $request->date);
        }

        // Filter by student_id
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by class
        if ($request->has('class')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('class', $request->class);
            });
        }

        $perPage = $request->get('per_page', 15);
        $attendances = $query->orderBy('date', 'desc')->paginate($perPage);

        return response()->json([
            'data' => AttendanceResource::collection($attendances->items()),
            'meta' => [
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
                'per_page' => $attendances->perPage(),
                'total' => $attendances->total(),
            ],
        ]);
    }

    /**
     * Store bulk attendance records.
     */
    public function storeBulk(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'students' => 'required|array|min:1',
            'students.*.student_id' => 'required|exists:students,id',
            'students.*.status' => 'required|in:present,absent,late',
            'students.*.note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->attendanceService->recordBulkAttendance(
            $validator->validated(),
            $request->user()->id
        );

        if (!$result['success']) {
            return response()->json([
                'message' => 'Failed to record attendance',
                'error' => $result['error'] ?? 'Unknown error',
            ], 500);
        }

        return response()->json([
            'message' => 'Attendance recorded successfully',
            'data' => $result,
        ], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late',
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['recorded_by'] = $request->user()->id;

        $attendance = Attendance::create($data);

        return response()->json([
            'message' => 'Attendance recorded successfully',
            'data' => new AttendanceResource($attendance->load('student', 'recorder')),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $attendance = Attendance::with(['student', 'recorder'])->findOrFail($id);

        return response()->json([
            'data' => new AttendanceResource($attendance),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $attendance = Attendance::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:present,absent,late',
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $attendance->update($validator->validated());

        return response()->json([
            'message' => 'Attendance updated successfully',
            'data' => new AttendanceResource($attendance->fresh()->load('student', 'recorder')),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();

        return response()->json([
            'message' => 'Attendance deleted successfully',
        ], 200);
    }

    /**
     * Get monthly attendance report.
     */
    public function monthlyReport(Request $request): JsonResponse
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $class = $request->get('class');

        $report = $this->attendanceService->generateMonthlyReport($month, $class);

        return response()->json([
            'data' => $report,
        ]);
    }

    /**
     * Get today's attendance statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $stats = $this->attendanceService->getAttendanceStatistics($date);

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Get student attendance history.
     */
    public function studentHistory(Request $request, string $studentId): JsonResponse
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Attendance::where('student_id', $studentId)
            ->with(['student', 'recorder'])
            ->orderBy('date', 'desc');

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        $perPage = $request->get('per_page', 30);
        $attendances = $query->paginate($perPage);

        // Calculate summary
        $total = $attendances->total();
        $present = Attendance::where('student_id', $studentId)
            ->where('status', 'present')
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->count();
        $absent = Attendance::where('student_id', $studentId)
            ->where('status', 'absent')
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->count();
        $late = Attendance::where('student_id', $studentId)
            ->where('status', 'late')
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->count();

        return response()->json([
            'data' => AttendanceResource::collection($attendances->items()),
            'summary' => [
                'total_records' => $total,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'attendance_percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
            ],
            'meta' => [
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
                'per_page' => $attendances->perPage(),
                'total' => $attendances->total(),
            ],
        ]);
    }

    /**
     * Get daily attendance report.
     */
    public function dailyReport(Request $request, string $date): JsonResponse
    {
        $class = $request->get('class');
        $section = $request->get('section');

        $query = Attendance::whereDate('date', $date)
            ->with(['student', 'recorder']);

        if ($class) {
            $query->whereHas('student', function ($q) use ($class) {
                $q->where('class', $class);
            });
        }

        if ($section) {
            $query->whereHas('student', function ($q) use ($section) {
                $q->where('section', $section);
            });
        }

        $attendances = $query->get();

        $summary = [
            'date' => $date,
            'total_students' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
        ];

        return response()->json([
            'data' => AttendanceResource::collection($attendances),
            'summary' => $summary,
        ]);
    }

    /**
     * Get comprehensive dashboard overview.
     */
    public function dashboardOverview(Request $request): JsonResponse
    {
        $today = Carbon::today()->format('Y-m-d');
        $currentMonth = Carbon::now()->format('Y-m');
        
        // Today's statistics
        $todayStats = $this->attendanceService->getAttendanceStatistics($today);
        
        // Weekly statistics
        $weekStart = Carbon::now()->startOfWeek()->format('Y-m-d');
        $weeklyReport = $this->attendanceService->generateWeeklyReport($weekStart);
        
        // Monthly statistics
        $monthlyReport = $this->attendanceService->generateMonthlyReport($currentMonth);
        
        // Low attendance alerts
        $lowAttendance = $this->attendanceService->getLowAttendanceStudents(75.0, $currentMonth);
        
        // Recent attendance (last 7 days)
        $recentDates = [];
        $recentStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $stats = $this->attendanceService->getAttendanceStatistics($date);
            $recentDates[] = $date;
            $recentStats[] = [
                'date' => $date,
                'present' => $stats['present'],
                'absent' => $stats['absent'],
                'late' => $stats['late'],
                'attendance_percentage' => $stats['attendance_percentage'],
            ];
        }

        // Class-wise summary
        $classes = \App\Models\SchoolClass::where('is_active', true)->get();
        $classSummary = [];
        foreach ($classes as $class) {
            $classStats = $this->attendanceService->getAttendanceStatistics($today);
            $classSummary[] = [
                'class_id' => $class->id,
                'class_name' => $class->name,
                'total_students' => $class->students()->count(),
                'present_today' => Attendance::whereDate('date', $today)
                    ->whereHas('student', fn($q) => $q->where('class_id', $class->id))
                    ->where('status', 'present')
                    ->count(),
            ];
        }

        return response()->json([
            'data' => [
                'today' => $todayStats,
                'weekly' => [
                    'summary' => [
                        'total_students' => $weeklyReport['total_students'],
                        'average_attendance' => $weeklyReport['total_students'] > 0
                            ? round(array_sum(array_column($weeklyReport['students'], 'attendance_percentage')) / $weeklyReport['total_students'], 2)
                            : 0,
                    ],
                    'daily_statistics' => $weeklyReport['daily_statistics'],
                ],
                'monthly' => [
                    'summary' => [
                        'total_students' => $monthlyReport['total_students'],
                        'average_attendance' => $monthlyReport['total_students'] > 0
                            ? round(array_sum(array_column($monthlyReport['students'], 'attendance_percentage')) / $monthlyReport['total_students'], 2)
                            : 0,
                    ],
                ],
                'recent_trends' => $recentStats,
                'low_attendance_alerts' => [
                    'count' => $lowAttendance['count'],
                    'students' => array_slice($lowAttendance['students'], 0, 10), // Top 10
                ],
                'class_summary' => $classSummary,
            ],
        ]);
    }
}
