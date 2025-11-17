# Events & Listeners Documentation

## Overview

The School Attendance Management System uses Laravel's event-driven architecture to handle notifications and logging for attendance-related activities.

## Events

### 1. AttendanceRecorded

**Triggered when:** Bulk attendance is recorded for a class/section

**Data:**
- `attendances`: Array of attendance records
- `date`: Date of attendance
- `class`: Class name
- `section`: Section name
- `summary`: Statistics (present, absent, late counts)

**Listeners:**
- `SendAttendanceNotification` - Sends notifications to administrators
- `LogAttendanceActivity` - Logs activity for audit trail

**Example:**
```php
event(new AttendanceRecorded($attendances, '2024-11-17', '10', 'A', [
    'present' => 25,
    'absent' => 3,
    'late' => 2,
    'total' => 30
]));
```

---

### 2. StudentMarkedAbsent

**Triggered when:** A student is marked absent

**Data:**
- `student`: Student model instance
- `attendance`: Attendance record
- `date`: Date of absence

**Listeners:**
- `NotifyAbsentStudent` - Notifies parents/guardians

**Example:**
```php
event(new StudentMarkedAbsent($student, $attendance, '2024-11-17'));
```

---

### 3. LowAttendanceDetected

**Triggered when:** A student's attendance falls below threshold

**Data:**
- `student`: Student model instance
- `attendancePercentage`: Current attendance percentage
- `threshold`: Minimum required percentage (default: 75%)
- `period`: Time period ('monthly', 'weekly', etc.)

**Listeners:**
- `SendLowAttendanceAlert` - Sends urgent alerts to parents and teachers

**Example:**
```php
event(new LowAttendanceDetected($student, 65.5, 75, 'monthly'));
```

---

### 4. ReportGenerated

**Triggered when:** An attendance report is generated

**Data:**
- `reportType`: Type of report ('daily', 'weekly', 'monthly')
- `reportData`: Report data array
- `period`: Time period (date or month)
- `class`: Class name (optional)
- `section`: Section name (optional)
- `filePath`: Path to generated file (optional)

**Listeners:**
- `NotifyReportGeneration` - Notifies administrators about report availability

**Example:**
```php
event(new ReportGenerated('monthly', $data, '2024-11', '10', 'A', '/path/to/report.csv'));
```

---

## Listeners

### SendAttendanceNotification

**Purpose:** Send notifications when attendance is recorded

**Actions:**
- Logs attendance recording details
- Sends email to administrators (TODO)
- Sends SMS notifications (TODO)
- Pushes mobile notifications (TODO)

**Configuration:**
```php
// config/mail.php
'admin_email' => env('ADMIN_EMAIL', 'admin@school.com'),
```

---

### NotifyAbsentStudent

**Purpose:** Notify parents when student is absent

**Actions:**
- Logs absence
- Sends email to parent/guardian (TODO)
- Sends SMS to parent phone (TODO)
- Creates in-app notification (TODO)

**Required Student Fields:**
- `parent_email` (optional)
- `parent_phone` (optional)

---

### SendLowAttendanceAlert

**Purpose:** Alert stakeholders about low attendance

**Actions:**
- Logs warning
- Sends urgent email to parents (TODO)
- Notifies class teacher (TODO)
- Creates high-priority admin notification (TODO)
- Sends SMS alert (TODO)

**Threshold Configuration:**
```php
// Default threshold: 75%
// Can be customized per event dispatch
```

---

### LogAttendanceActivity

**Purpose:** Maintain audit trail of attendance activities

**Actions:**
- Logs to daily log file
- Stores in activity log table (TODO)
- Updates real-time dashboard cache (TODO)

**Log Location:**
```
storage/logs/laravel-YYYY-MM-DD.log
```

---

### NotifyReportGeneration

**Purpose:** Notify about generated reports

**Actions:**
- Logs report generation
- Emails report to administrators (TODO)
- Notifies relevant teachers (TODO)
- Creates dashboard notification (TODO)

---

## Usage Examples

### Manually Dispatching Events

```php
use App\Events\AttendanceRecorded;
use App\Events\StudentMarkedAbsent;
use App\Events\LowAttendanceDetected;
use App\Events\ReportGenerated;

// Dispatch attendance recorded event
event(new AttendanceRecorded($attendances, $date, $class, $section, $summary));

// Dispatch student absent event
event(new StudentMarkedAbsent($student, $attendance, $date));

// Dispatch low attendance alert
event(new LowAttendanceDetected($student, 65.5, 75, 'monthly'));

// Dispatch report generated event
event(new ReportGenerated('monthly', $data, '2024-11', '10', 'A', $filePath));
```

### Automatic Event Dispatching

Events are automatically dispatched in:

1. **AttendanceService::recordBulkAttendance()**
   - Dispatches `AttendanceRecorded` after successful bulk save
   - Dispatches `StudentMarkedAbsent` for each absent student

2. **GenerateAttendanceReport Command**
   - Dispatches `ReportGenerated` after report export

### Checking Low Attendance

To implement automatic low attendance detection, add to your scheduler:

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Check for low attendance daily
    $schedule->call(function () {
        $students = Student::all();
        $threshold = 75;
        
        foreach ($students as $student) {
            $percentage = $this->calculateMonthlyAttendance($student);
            
            if ($percentage < $threshold) {
                event(new LowAttendanceDetected($student, $percentage, $threshold, 'monthly'));
            }
        }
    })->daily();
}
```

---

## Extending the System

### Adding Email Notifications

1. Create Mailable classes:
```bash
php artisan make:mail AttendanceRecordedMail
php artisan make:mail StudentAbsentMail
php artisan make:mail LowAttendanceAlertMail
```

2. Update listeners to send emails:
```php
// In SendAttendanceNotification listener
Mail::to(config('mail.admin_email'))->send(new AttendanceRecordedMail($event));
```

### Adding SMS Notifications

1. Install SMS package (e.g., Twilio):
```bash
composer require twilio/sdk
```

2. Configure in `.env`:
```env
TWILIO_SID=your_sid
TWILIO_TOKEN=your_token
TWILIO_FROM=your_phone_number
```

3. Implement in listeners:
```php
use Twilio\Rest\Client;

$twilio = new Client(config('services.twilio.sid'), config('services.twilio.token'));
$twilio->messages->create($phoneNumber, [
    'from' => config('services.twilio.from'),
    'body' => $message
]);
```

### Adding Database Notifications

1. Create notifications table:
```bash
php artisan notifications:table
php artisan migrate
```

2. Create notification model and implement in listeners

### Adding Slack/Discord Webhooks

1. Configure webhook URL in `.env`:
```env
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/...
```

2. Send webhook in listeners:
```php
use Illuminate\Support\Facades\Http;

Http::post(config('services.slack.webhook'), [
    'text' => "Attendance recorded for Class {$event->class}"
]);
```

---

## Testing Events

### Unit Testing

```php
use Illuminate\Support\Facades\Event;
use App\Events\AttendanceRecorded;

public function test_attendance_recorded_event_is_dispatched()
{
    Event::fake();
    
    // Record attendance
    $this->attendanceService->recordBulkAttendance($data, $userId);
    
    // Assert event was dispatched
    Event::assertDispatched(AttendanceRecorded::class);
}
```

### Listener Testing

```php
public function test_attendance_notification_is_sent()
{
    Mail::fake();
    
    $event = new AttendanceRecorded($attendances, $date, $class, $section, $summary);
    $listener = new SendAttendanceNotification();
    $listener->handle($event);
    
    Mail::assertSent(AttendanceRecordedMail::class);
}
```

---

## Troubleshooting

### Events Not Firing

1. Check EventServiceProvider registration
2. Clear cache: `php artisan event:clear`
3. Check logs: `storage/logs/laravel.log`

### Listeners Not Executing

1. Verify listener is registered in EventServiceProvider
2. Check for exceptions in listener code
3. Enable debug mode: `APP_DEBUG=true`

### Queue Issues

If using queued listeners:
```bash
php artisan queue:work
```

---

## Best Practices

1. **Keep listeners focused** - Each listener should do one thing
2. **Use queues for slow operations** - Email/SMS should be queued
3. **Log important events** - Always log for audit trail
4. **Handle failures gracefully** - Don't let listener failures break the app
5. **Test thoroughly** - Write tests for events and listeners
6. **Document custom events** - Update this file when adding new events

---

## Future Enhancements

- [ ] Implement email notifications
- [ ] Add SMS integration
- [ ] Create in-app notification system
- [ ] Add Slack/Discord webhooks
- [ ] Implement parent portal notifications
- [ ] Add mobile push notifications
- [ ] Create notification preferences system
- [ ] Add notification history/logs
