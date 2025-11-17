# Artisan Commands Documentation

## Attendance Report Generator

Generate monthly attendance reports from the command line with export capabilities.

### Command Signature

```bash
php artisan attendance:generate-report {month} {class} [options]
```

### Arguments

- **month** (required): The month in `YYYY-MM` format (e.g., `2024-11`)
- **class** (required): The class name (e.g., `10`, `Class 10`)

### Options

- **--section=**: Optional section name (e.g., `A`, `B`)
- **--format=**: Export format - `csv`, `json`, or `table` (default: `csv`)
- **--output=**: Custom output file path (optional)

### Usage Examples

#### 1. Generate CSV Report for Class 10
```bash
php artisan attendance:generate-report 2024-11 "10"
```
Output: `storage/app/reports/attendance_2024-11_class_10.csv`

#### 2. Generate Report for Specific Section
```bash
php artisan attendance:generate-report 2024-11 "10" --section=A
```
Output: `storage/app/reports/attendance_2024-11_class_10_section_A.csv`

#### 3. Display Report in Console Table
```bash
php artisan attendance:generate-report 2024-11 "10" --format=table
```

#### 4. Export as JSON
```bash
php artisan attendance:generate-report 2024-11 "10" --format=json
```
Output: `storage/app/reports/attendance_2024-11_class_10.json`

#### 5. Custom Output Path
```bash
php artisan attendance:generate-report 2024-11 "10" --output=/path/to/custom/report.csv
```

### Report Contents

The generated report includes:

- **Student ID**: Unique student identifier
- **Name**: Student's full name
- **Class**: Student's class
- **Section**: Student's section
- **Present Days**: Number of days marked present
- **Absent Days**: Number of days marked absent
- **Late Days**: Number of days marked late
- **Recorded Days**: Total days attendance was recorded
- **School Days**: Total working days in the month (excluding weekends and holidays)
- **Attendance %**: Percentage calculated as (Present Days / Recorded Days) × 100

### Output Formats

#### CSV Format
- Standard comma-separated values
- Headers included
- Compatible with Excel, Google Sheets
- Location: `storage/app/reports/`

#### JSON Format
- Structured JSON with metadata
- Includes generation timestamp
- Total student count
- Array of student records
- Location: `storage/app/reports/`

#### Table Format
- Displays in console
- Includes summary statistics
- No file output

### Summary Statistics (Table Format Only)

When using `--format=table`, the command displays:
- Total Students
- Total Present Days
- Total Absent Days
- Total Late Days
- Average Attendance Percentage

### Error Handling

The command validates:
- Month format (must be YYYY-MM)
- Class existence (checks if students exist)
- Section existence (if specified)

### Scheduling (Optional)

You can schedule this command to run automatically using Laravel's task scheduler.

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Generate monthly report on the 1st of each month
    $schedule->command('attendance:generate-report', [
        now()->subMonth()->format('Y-m'),
        '10',
        '--section=A'
    ])->monthlyOn(1, '08:00');
}
```

### Permissions

Ensure the `storage/app/reports/` directory is writable:

```bash
chmod -R 775 storage/app/reports/
```

### Examples with Real Data

```bash
# Generate November 2024 report for Class 10, Section A
php artisan attendance:generate-report 2024-11 "10" --section=A

# Generate report for all sections of Class 9
php artisan attendance:generate-report 2024-11 "9"

# View October report in console
php artisan attendance:generate-report 2024-10 "8" --format=table

# Export December report as JSON
php artisan attendance:generate-report 2024-12 "7" --format=json
```

### Troubleshooting

**Error: "Invalid month format"**
- Ensure month is in YYYY-MM format
- Example: `2024-11` not `11-2024` or `2024/11`

**Error: "No students found"**
- Check if the class name matches exactly
- Verify students exist in the database
- Check section name if using --section option

**Permission denied**
- Run: `chmod -R 775 storage/app/reports/`
- Or: `sudo chown -R www-data:www-data storage/`

### Notes

- Reports are saved in `storage/app/reports/` by default
- Existing files with the same name will be overwritten
- The command shows a progress bar while processing
- School days calculation excludes weekends and holidays
- Attendance percentage is rounded to 2 decimal places
