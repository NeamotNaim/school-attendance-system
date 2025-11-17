<?php

namespace App\Listeners;

use App\Events\AttendanceRecorded;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAttendanceNotification
{
    /**
     * Handle the event.
     */
    public function handle(AttendanceRecorded $event)
    {
        // Log attendance recording
        Log::info('Attendance recorded', [
            'date' => $event->date,
            'class' => $event->class,
            'section' => $event->section,
            'total_students' => count($event->attendances),
            'present' => $event->summary['present'] ?? 0,
            'absent' => $event->summary['absent'] ?? 0,
            'late' => $event->summary['late'] ?? 0,
        ]);

        // Check if notification already exists for this date/class/section (prevent duplicates)
        $recentNotifications = Notification::where('type', 'attendance_recorded')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->get();

        $isDuplicate = false;
        foreach ($recentNotifications as $notification) {
            $data = $notification->data;
            if (isset($data['date'], $data['class'], $data['section']) &&
                $data['date'] === $event->date &&
                $data['class'] === $event->class &&
                $data['section'] === $event->section) {
                $isDuplicate = true;
                break;
            }
        }

        if (!$isDuplicate) {
            // Create in-app notification
            Notification::create([
                'type' => 'attendance_recorded',
                'title' => 'Attendance Recorded',
                'message' => "Attendance recorded for Class {$event->class} Section {$event->section} on {$event->date}",
                'data' => [
                    'date' => $event->date,
                    'class' => $event->class,
                    'section' => $event->section,
                    'summary' => $event->summary,
                ],
                'icon' => '✓',
                'color' => 'green',
                'priority' => 'normal',
                'user_id' => null, // Visible to all users
            ]);
        }

        // TODO: Send email notification to administrators
        // Mail::to(config('mail.admin_email'))->send(new AttendanceRecordedMail($event));

        // TODO: Send SMS notifications if configured
        // $this->sendSmsNotification($event);

        // TODO: Push notification to mobile app
        // $this->sendPushNotification($event);
    }
}
