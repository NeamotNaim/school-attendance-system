<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use App\Models\Attendance;

class AttendanceRecorded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Attendance $attendance;

    /**
     * Create a new event instance.
     */
    public function __construct(Attendance $attendance)
    {
        $this->attendance = $attendance;
    }
}
