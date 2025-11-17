<?php

namespace App\Providers;

use App\Events\AttendanceRecorded;
use App\Events\LowAttendanceDetected;
use App\Events\StudentMarkedAbsent;
use App\Events\ReportGenerated;
use App\Listeners\SendAttendanceNotification;
use App\Listeners\SendLowAttendanceAlert;
use App\Listeners\NotifyAbsentStudent;
use App\Listeners\LogAttendanceActivity;
use App\Listeners\NotifyReportGeneration;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        AttendanceRecorded::class => [
            SendAttendanceNotification::class,
            LogAttendanceActivity::class,
        ],
        StudentMarkedAbsent::class => [
            NotifyAbsentStudent::class,
        ],
        LowAttendanceDetected::class => [
            SendLowAttendanceAlert::class,
        ],
        ReportGenerated::class => [
            NotifyReportGeneration::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

