<?php

namespace App\Listeners;

use App\Events\AttendanceRecorded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendAttendanceNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AttendanceRecorded $event): void
    {
        $attendance = $event->attendance;
        
        // Log attendance notification
        Log::info('Attendance recorded', [
            'student_id' => $attendance->student_id,
            'date' => $attendance->date,
            'status' => $attendance->status,
            'recorded_by' => $attendance->recorded_by,
        ]);

        // Here you could send email notifications, SMS, etc.
        // For now, we'll just log it
    }
}
