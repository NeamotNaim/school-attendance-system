<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'date',
        'description',
        'type',
        'is_recurring',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
    ];

    /**
     * Check if a date is a holiday.
     */
    public static function isHoliday(Carbon $date): bool
    {
        return static::whereDate('date', $date)->exists();
    }

    /**
     * Get holidays for a date range.
     */
    public static function forDateRange(Carbon $start, Carbon $end)
    {
        return static::whereBetween('date', [$start, $end])->get();
    }
}
