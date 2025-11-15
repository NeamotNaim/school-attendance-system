<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class HolidayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Holiday::query();

        if ($request->has('start_date') && $request->has('end_date')) {
            $start = Carbon::parse($request->start_date);
            $end = Carbon::parse($request->end_date);
            $query->whereBetween('date', [$start, $end]);
        } elseif ($request->has('year')) {
            $year = $request->year;
            $query->whereYear('date', $year);
        } elseif ($request->has('month')) {
            $month = Carbon::parse($request->month);
            $query->whereYear('date', $month->year)
                  ->whereMonth('date', $month->month);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $holidays = $query->orderBy('date')->get();

        return response()->json([
            'data' => $holidays,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'type' => 'required|in:holiday,exam,event',
            'is_recurring' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $holiday = Holiday::create($validator->validated());

        return response()->json([
            'message' => 'Holiday created successfully',
            'data' => $holiday,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $holiday = Holiday::findOrFail($id);

        return response()->json([
            'data' => $holiday,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $holiday = Holiday::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'date' => 'sometimes|date',
            'description' => 'nullable|string',
            'type' => 'sometimes|in:holiday,exam,event',
            'is_recurring' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $holiday->update($validator->validated());

        return response()->json([
            'message' => 'Holiday updated successfully',
            'data' => $holiday->fresh(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $holiday = Holiday::findOrFail($id);
        $holiday->delete();

        return response()->json([
            'message' => 'Holiday deleted successfully',
        ], 200);
    }

    /**
     * Check if a date is a holiday.
     */
    public function checkDate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $date = Carbon::parse($request->date);
        $isHoliday = Holiday::isHoliday($date);
        $holiday = $isHoliday ? Holiday::whereDate('date', $date)->first() : null;

        return response()->json([
            'date' => $date->format('Y-m-d'),
            'is_holiday' => $isHoliday,
            'holiday' => $holiday,
        ]);
    }
}
