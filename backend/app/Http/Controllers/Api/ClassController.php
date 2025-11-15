<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SchoolClass::query();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $classes = $query->with('sections')->get();

        return response()->json([
            'data' => $classes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:classes,code|max:50',
            'description' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $class = SchoolClass::create($validator->validated());

        return response()->json([
            'message' => 'Class created successfully',
            'data' => $class->load('sections'),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $class = SchoolClass::with(['sections', 'students'])->findOrFail($id);

        return response()->json([
            'data' => $class,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $class = SchoolClass::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|unique:classes,code,' . $id . '|max:50',
            'description' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $class->update($validator->validated());

        return response()->json([
            'message' => 'Class updated successfully',
            'data' => $class->fresh()->load('sections'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $class = SchoolClass::findOrFail($id);

        // Check if class has students
        if ($class->students()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete class with students. Please reassign students first.',
            ], 422);
        }

        $class->delete();

        return response()->json([
            'message' => 'Class deleted successfully',
        ], 200);
    }
}
