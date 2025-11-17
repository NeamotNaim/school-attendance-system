<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceRecorded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $attendances;
    public $date;
    public $class;
    public $section;
    public $summary;

    /**
     * Create a new event instance.
     */
    public function __construct($attendances, $date, $class, $section, $summary)
    {
        $this->attendances = $attendances;
        $this->date = $date;
        $this->class = $class;
        $this->section = $section;
        $this->summary = $summary;
    }
}
