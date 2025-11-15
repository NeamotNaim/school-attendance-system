<?php

namespace App\Events;

use App\Models\Student;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowAttendanceDetected
{
    use Dispatchable, SerializesModels;

    public Student $student;
    public float $attendancePercentage;
    public float $threshold;
    public string $month;

    /**
     * Create a new event instance.
     */
    public function __construct(Student $student, float $attendancePercentage, float $threshold, string $month)
    {
        $this->student = $student;
        $this->attendancePercentage = $attendancePercentage;
        $this->threshold = $threshold;
        $this->month = $month;
    }
}
