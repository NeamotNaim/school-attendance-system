<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportGenerated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reportType;
    public $reportData;
    public $period;
    public $class;
    public $section;
    public $filePath;

    /**
     * Create a new event instance.
     */
    public function __construct($reportType, $reportData, $period, $class = null, $section = null, $filePath = null)
    {
        $this->reportType = $reportType; // 'daily', 'weekly', 'monthly'
        $this->reportData = $reportData;
        $this->period = $period;
        $this->class = $class;
        $this->section = $section;
        $this->filePath = $filePath;
    }
}
