<?php

namespace App\Listeners;

use App\Events\LowAttendanceDetected;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendLowAttendanceAlert
{
    /**
     * Handle the event.
     */
    public function handle(LowAttendanceDetected $event)
    {
        $student = $event->student;
        $percentage = $event->attendancePercentage;
        $threshold = $event->threshold;

        // Log the low attendance alert
        Log::warning('Low attendance detected', [
            'student_id' => $student->student_id,
            'name' => $student->name,
            'class' => $student->class,
            'section' => $student->section,
            'attendance_percentage' => $percentage,
            'threshold' => $threshold,
            'period' => $event->period,
        ]);

        // Create urgent in-app notification
        Notification::create([
            'type' => 'low_attendance_alert',
            'title' => 'Low Attendance Alert',
            'message' => "{$student->name} has low attendance: {$percentage}% (threshold: {$threshold}%)",
            'data' => [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'class' => $student->class,
                'section' => $student->section,
                'percentage' => $percentage,
                'threshold' => $threshold,
                'period' => $event->period,
            ],
            'icon' => '🚨',
            'color' => 'red',
            'priority' => 'urgent',
            'user_id' => null,
        ]);

        // TODO: Send urgent notification to parents
        // if ($student->parent_email) {
        //     Mail::to($student->parent_email)->send(new LowAttendanceAlertMail($student, $percentage, $threshold));
        // }

        // TODO: Notify class teacher
        // if ($student->class_teacher_email) {
        //     Mail::to($student->class_teacher_email)->send(new LowAttendanceTeacherMail($student, $percentage));
        // }

        // TODO: Send SMS alert
        // if ($student->parent_phone) {
        //     $message = "ALERT: {$student->name}'s attendance is {$percentage}%, below the required {$threshold}%";
        //     $this->sendSms($student->parent_phone, $message);
        // }
    }
}
