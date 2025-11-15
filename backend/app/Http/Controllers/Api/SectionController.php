<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Section::with('schoolClass');

        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $sections = $query->get();

        return response()->json([
            'data' => $sections,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:sections,code|max:50',
            'capacity' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check unique name within class
        $existing = Section::where('class_id', $request->class_id)
            ->where('name', $request->name)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Section with this name already exists in this class',
                'errors' => ['name' => ['Section name must be unique within a class']],
            ], 422);
        }

        $section = Section::create($validator->validated());

        return response()->json([
            'message' => 'Section created successfully',
            'data' => $section->load('schoolClass'),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $section = Section::with(['schoolClass', 'students'])->findOrFail($id);

        return response()->json([
            'data' => $section,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $section = Section::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'class_id' => 'sometimes|exists:classes,id',
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|unique:sections,code,' . $id . '|max:50',
            'capacity' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check unique name within class if name or class_id is being updated
        if ($request->has('name') || $request->has('class_id')) {
            $classId = $request->class_id ?? $section->class_id;
            $name = $request->name ?? $section->name;

            $existing = Section::where('class_id', $classId)
                ->where('name', $name)
                ->where('id', '!=', $id)
                ->first();

            if ($existing) {
                return response()->json([
                    'message' => 'Section with this name already exists in this class',
                    'errors' => ['name' => ['Section name must be unique within a class']],
                ], 422);
            }
        }

        $section->update($validator->validated());

        return response()->json([
            'message' => 'Section updated successfully',
            'data' => $section->fresh()->load('schoolClass'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $section = Section::findOrFail($id);

        // Check if section has students
        if ($section->students()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete section with students. Please reassign students first.',
            ], 422);
        }

        $section->delete();

        return response()->json([
            'message' => 'Section deleted successfully',
        ], 200);
    }
}
