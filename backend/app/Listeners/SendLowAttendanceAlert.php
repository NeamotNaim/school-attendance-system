<?php

namespace App\Listeners;

use App\Events\LowAttendanceDetected;
use App\Notifications\LowAttendanceNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendLowAttendanceAlert implements ShouldQueue
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
    public function handle(LowAttendanceDetected $event): void
    {
        $student = $event->student;
        
        Log::info('Low attendance detected', [
            'student_id' => $student->id,
            'student_name' => $student->name,
            'attendance_percentage' => $event->attendancePercentage,
            'threshold' => $event->threshold,
            'month' => $event->month,
        ]);

        // Notify all admin users
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new LowAttendanceNotification(
                $student,
                $event->attendancePercentage,
                $event->threshold,
                $event->month
            ));
        }

        // Optionally notify guardians if email is available
        if ($student->email && filter_var($student->email, FILTER_VALIDATE_EMAIL)) {
            // You can add guardian notification here
        }
    }
}
