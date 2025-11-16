<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = SchoolClass::all();

        if ($classes->isEmpty()) {
            $this->command->error('No classes found. Please run ClassSeeder first.');
            return;
        }

        $firstNames = [
            'John', 'Emma', 'Michael', 'Sophia', 'William', 'Olivia', 'James', 'Ava',
            'Robert', 'Isabella', 'David', 'Mia', 'Joseph', 'Charlotte', 'Thomas', 'Amelia',
            'Daniel', 'Harper', 'Matthew', 'Evelyn', 'Christopher', 'Abigail', 'Andrew', 'Emily',
            'Joshua', 'Elizabeth', 'Ryan', 'Sofia', 'Nicholas', 'Avery', 'Alexander', 'Ella',
            'Jacob', 'Madison', 'Ethan', 'Scarlett', 'Benjamin', 'Victoria', 'Samuel', 'Aria',
            'Lucas', 'Grace', 'Henry', 'Chloe', 'Nathan', 'Camila', 'Isaac', 'Penelope',
            'Caleb', 'Riley', 'Jack', 'Layla', 'Christian', 'Lillian', 'Jonathan', 'Nora',
            'Liam', 'Zoey', 'Mason', 'Mila', 'Noah', 'Aubrey', 'Elijah', 'Hannah',
        ];

        $lastNames = [
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
            'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas',
            'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Thompson', 'White', 'Harris',
            'Clark', 'Lewis', 'Robinson', 'Walker', 'Young', 'Allen', 'King', 'Wright',
        ];

        $sections = ['A', 'B', 'C'];
        $studentCounter = 1;

        foreach ($classes as $class) {
            foreach ($sections as $section) {
                // Create 25-30 students per section
                $studentsPerSection = rand(25, 30);

                for ($i = 1; $i <= $studentsPerSection; $i++) {
                    $firstName = $firstNames[array_rand($firstNames)];
                    $lastName = $lastNames[array_rand($lastNames)];

                    Student::create([
                        'student_id' => 'STU' . str_pad($studentCounter, 4, '0', STR_PAD_LEFT),
                        'name' => $firstName . ' ' . $lastName,
                        'class' => $class->name,
                        'section' => $section,
                        'photo' => null,
                    ]);

                    $studentCounter++;
                }
            }
        }

        $this->command->info('Students seeded successfully! Total: ' . ($studentCounter - 1));
    }
}
