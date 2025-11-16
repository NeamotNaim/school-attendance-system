<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = Carbon::now()->year;

        $holidays = [
            // National Holidays
            [
                'title' => 'New Year\'s Day',
                'date' => Carbon::create($currentYear, 1, 1),
                'type' => 'holiday',
                'description' => 'First day of the year',
                'is_recurring' => true,
            ],
            [
                'title' => 'Martin Luther King Jr. Day',
                'date' => Carbon::create($currentYear, 1, 15),
                'type' => 'holiday',
                'description' => 'Honoring civil rights leader',
                'is_recurring' => true,
            ],
            [
                'title' => 'Presidents\' Day',
                'date' => Carbon::create($currentYear, 2, 19),
                'type' => 'holiday',
                'description' => 'Honoring U.S. presidents',
                'is_recurring' => true,
            ],
            [
                'title' => 'Spring Break',
                'date' => Carbon::create($currentYear, 3, 25),
                'type' => 'holiday',
                'description' => 'Week-long spring vacation',
                'is_recurring' => false,
            ],
            [
                'title' => 'Memorial Day',
                'date' => Carbon::create($currentYear, 5, 27),
                'type' => 'holiday',
                'description' => 'Honoring military personnel',
                'is_recurring' => true,
            ],
            [
                'title' => 'Independence Day',
                'date' => Carbon::create($currentYear, 7, 4),
                'type' => 'holiday',
                'description' => 'U.S. Independence Day',
                'is_recurring' => true,
            ],
            [
                'title' => 'Labor Day',
                'date' => Carbon::create($currentYear, 9, 2),
                'type' => 'holiday',
                'description' => 'Celebrating workers',
                'is_recurring' => true,
            ],
            [
                'title' => 'Thanksgiving Break',
                'date' => Carbon::create($currentYear, 11, 28),
                'type' => 'holiday',
                'description' => 'Thanksgiving holiday',
                'is_recurring' => true,
            ],
            [
                'title' => 'Christmas Break Start',
                'date' => Carbon::create($currentYear, 12, 23),
                'type' => 'holiday',
                'description' => 'Winter vacation begins',
                'is_recurring' => true,
            ],
            [
                'title' => 'Christmas Day',
                'date' => Carbon::create($currentYear, 12, 25),
                'type' => 'holiday',
                'description' => 'Christmas celebration',
                'is_recurring' => true,
            ],

            // Exams
            [
                'title' => 'Mid-Term Exams Begin',
                'date' => Carbon::create($currentYear, 3, 10),
                'type' => 'exam',
                'description' => 'First semester mid-term examinations',
                'is_recurring' => false,
            ],
            [
                'title' => 'Mid-Term Exams End',
                'date' => Carbon::create($currentYear, 3, 17),
                'type' => 'exam',
                'description' => 'End of mid-term examinations',
                'is_recurring' => false,
            ],
            [
                'title' => 'Final Exams Begin',
                'date' => Carbon::create($currentYear, 6, 5),
                'type' => 'exam',
                'description' => 'Final examinations start',
                'is_recurring' => false,
            ],
            [
                'title' => 'Final Exams End',
                'date' => Carbon::create($currentYear, 6, 15),
                'type' => 'exam',
                'description' => 'End of final examinations',
                'is_recurring' => false,
            ],
            [
                'title' => 'Fall Semester Exams',
                'date' => Carbon::create($currentYear, 11, 15),
                'type' => 'exam',
                'description' => 'Fall semester examinations',
                'is_recurring' => false,
            ],

            // School Events
            [
                'title' => 'Back to School Night',
                'date' => Carbon::create($currentYear, 9, 10),
                'type' => 'event',
                'description' => 'Meet the teachers and staff',
                'is_recurring' => true,
            ],
            [
                'title' => 'Science Fair',
                'date' => Carbon::create($currentYear, 4, 20),
                'type' => 'event',
                'description' => 'Annual science fair exhibition',
                'is_recurring' => true,
            ],
            [
                'title' => 'Sports Day',
                'date' => Carbon::create($currentYear, 5, 15),
                'type' => 'event',
                'description' => 'Annual sports competition',
                'is_recurring' => true,
            ],
            [
                'title' => 'Parent-Teacher Conference',
                'date' => Carbon::create($currentYear, 10, 25),
                'type' => 'event',
                'description' => 'Discuss student progress',
                'is_recurring' => true,
            ],
            [
                'title' => 'Winter Concert',
                'date' => Carbon::create($currentYear, 12, 15),
                'type' => 'event',
                'description' => 'Annual winter music concert',
                'is_recurring' => true,
            ],
            [
                'title' => 'Graduation Ceremony',
                'date' => Carbon::create($currentYear, 6, 20),
                'type' => 'event',
                'description' => 'Celebrating graduating students',
                'is_recurring' => true,
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::create($holiday);
        }

        $this->command->info('Holidays seeded successfully!');
    }
}
