<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Student::query();

        // Filter by class
        if ($request->has('class')) {
            $query->where('class', $request->class);
        }

        // Filter by section
        if ($request->has('section')) {
            $query->where('section', $request->section);
        }

        // Search by name or student_id
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 15);
        $students = $query->paginate($perPage);

        return response()->json([
            'data' => StudentResource::collection($students->items()),
            'meta' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|string|unique:students,student_id|max:255',
            'name' => 'required|string|max:255',
            'class' => 'required|string|max:255',
            'section' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('students', 'public');
        }

        $student = Student::create($data);

        return response()->json([
            'message' => 'Student created successfully',
            'data' => new StudentResource($student),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $student = Student::findOrFail($id);

        return response()->json([
            'data' => new StudentResource($student),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $student = Student::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'student_id' => 'sometimes|string|unique:students,student_id,' . $id . '|max:255',
            'name' => 'sometimes|string|max:255',
            'class' => 'sometimes|string|max:255',
            'section' => 'sometimes|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($student->photo) {
                \Storage::disk('public')->delete($student->photo);
            }
            $data['photo'] = $request->file('photo')->store('students', 'public');
        }

        $student->update($data);

        return response()->json([
            'message' => 'Student updated successfully',
            'data' => new StudentResource($student->fresh()),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $student = Student::findOrFail($id);

        // Delete photo if exists
        if ($student->photo) {
            \Storage::disk('public')->delete($student->photo);
        }

        $student->delete();

        return response()->json([
            'message' => 'Student deleted successfully',
        ], 200);
    }

    /**
     * Get student attendance history.
     */
    public function attendanceHistory(string $id): JsonResponse
    {
        $student = Student::findOrFail($id);

        $attendances = $student->attendances()
            ->with('recorder')
            ->orderBy('date', 'desc')
            ->paginate(30);

        return response()->json([
            'student' => new StudentResource($student),
            'data' => \App\Http\Resources\AttendanceResource::collection($attendances->items()),
            'meta' => [
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
                'per_page' => $attendances->perPage(),
                'total' => $attendances->total(),
            ],
        ]);
    }
}
