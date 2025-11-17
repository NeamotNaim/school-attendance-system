<?php

namespace App\Listeners;

use App\Events\ReportGenerated;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyReportGeneration
{
    /**
     * Handle the event.
     */
    public function handle(ReportGenerated $event)
    {
        // Log report generation
        Log::info('Report generated', [
            'type' => $event->reportType,
            'period' => $event->period,
            'class' => $event->class,
            'section' => $event->section,
            'file_path' => $event->filePath,
            'generated_at' => now()->toDateTimeString(),
        ]);

        // Create in-app notification
        $classSection = $event->class ? " for Class {$event->class}" : '';
        $classSection .= $event->section ? " Section {$event->section}" : '';
        
        Notification::create([
            'type' => 'report_generated',
            'title' => 'Report Generated',
            'message' => ucfirst($event->reportType) . " report generated for {$event->period}{$classSection}",
            'data' => [
                'report_type' => $event->reportType,
                'period' => $event->period,
                'class' => $event->class,
                'section' => $event->section,
                'file_path' => $event->filePath,
            ],
            'icon' => '📊',
            'color' => 'blue',
            'priority' => 'normal',
            'user_id' => null,
        ]);

        // TODO: Send report to administrators
        // if ($event->filePath) {
        //     Mail::to(config('mail.admin_email'))
        //         ->send(new ReportGeneratedMail($event->reportType, $event->period, $event->filePath));
        // }

        // TODO: Notify relevant teachers
        // if ($event->class) {
        //     $teachers = Teacher::where('class', $event->class)->get();
        //     foreach ($teachers as $teacher) {
        //         Mail::to($teacher->email)->send(new ClassReportMail($event));
        //     }
        // }
    }
}
