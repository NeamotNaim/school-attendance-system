<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowAttendanceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Student $student;
    public float $attendancePercentage;
    public float $threshold;
    public string $month;

    /**
     * Create a new notification instance.
     */
    public function __construct(Student $student, float $attendancePercentage, float $threshold, string $month)
    {
        $this->student = $student;
        $this->attendancePercentage = $attendancePercentage;
        $this->threshold = $threshold;
        $this->month = $month;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Low Attendance Alert - ' . $this->student->name)
            ->line("Student {$this->student->name} (ID: {$this->student->student_id}) has low attendance.")
            ->line("Attendance Percentage: {$this->attendancePercentage}% (Threshold: {$this->threshold}%)")
            ->line("Month: {$this->month}")
            ->line("Class: {$this->student->className} - Section: {$this->student->sectionName}")
            ->action('View Student Details', url('/students/' . $this->student->id))
            ->line('Please take necessary action.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'student_id' => $this->student->id,
            'student_name' => $this->student->name,
            'student_id_code' => $this->student->student_id,
            'attendance_percentage' => $this->attendancePercentage,
            'threshold' => $this->threshold,
            'month' => $this->month,
            'class' => $this->student->className,
            'section' => $this->student->sectionName,
            'message' => "Student {$this->student->name} has attendance below {$this->threshold}% for {$this->month}",
        ];
    }
}
