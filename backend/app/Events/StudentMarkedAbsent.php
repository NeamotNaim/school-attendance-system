<?php

namespace App\Events;

use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentMarkedAbsent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $student;
    public $attendance;
    public $date;

    /**
     * Create a new event instance.
     */
    public function __construct(Student $student, Attendance $attendance, $date)
    {
        $this->student = $student;
        $this->attendance = $attendance;
        $this->date = $date;
    }
}
