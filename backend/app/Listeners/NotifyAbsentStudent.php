<?php

namespace App\Listeners;

use App\Events\StudentMarkedAbsent;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyAbsentStudent
{
    /**
     * Handle the event.
     */
    public function handle(StudentMarkedAbsent $event)
    {
        $student = $event->student;
        $date = $event->date;

        // Log the absence
        Log::info('Student marked absent', [
            'student_id' => $student->student_id,
            'name' => $student->name,
            'class' => $student->class,
            'section' => $student->section,
            'date' => $date,
        ]);

        // In-app notification disabled - too many notifications for individual absences
        // Only low attendance alerts will be shown
        // Notification::create([
        //     'type' => 'student_absent',
        //     'title' => 'Student Absent',
        //     'message' => "{$student->name} ({$student->student_id}) was marked absent on {$date}",
        //     'data' => [
        //         'student_id' => $student->id,
        //         'student_name' => $student->name,
        //         'class' => $student->class,
        //         'section' => $student->section,
        //         'date' => $date,
        //     ],
        //     'icon' => '⚠',
        //     'color' => 'yellow',
        //     'priority' => 'normal',
        //     'user_id' => null,
        // ]);

        // TODO: Send notification to parents/guardians
        // if ($student->parent_email) {
        //     Mail::to($student->parent_email)->send(new StudentAbsentMail($student, $date));
        // }

        // TODO: Send SMS to parent phone number
        // if ($student->parent_phone) {
        //     $this->sendSms($student->parent_phone, "Your child {$student->name} was absent on {$date}");
        // }
    }
}
