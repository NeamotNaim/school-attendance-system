<?php

namespace App\Listeners;

use App\Events\AttendanceRecorded;
use Illuminate\Support\Facades\Log;

class LogAttendanceActivity
{
    /**
     * Handle the event.
     */
    public function handle(AttendanceRecorded $event)
    {
        // Detailed activity logging
        $logData = [
            'action' => 'attendance_recorded',
            'date' => $event->date,
            'class' => $event->class,
            'section' => $event->section,
            'timestamp' => now()->toDateTimeString(),
            'summary' => $event->summary,
            'total_records' => count($event->attendances),
        ];

        // Log to file
        Log::channel('daily')->info('Attendance Activity', $logData);

        // TODO: Store in activity log table
        // ActivityLog::create([
        //     'type' => 'attendance_recorded',
        //     'description' => "Attendance recorded for Class {$event->class} Section {$event->section}",
        //     'data' => json_encode($logData),
        //     'user_id' => auth()->id(),
        //     'created_at' => now(),
        // ]);

        // TODO: Update real-time dashboard statistics
        // Cache::put("attendance_stats_{$event->date}", $event->summary, now()->addHours(24));
    }
}
