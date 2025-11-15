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
}
