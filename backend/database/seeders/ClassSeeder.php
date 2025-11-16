<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = [
            [
                'name' => '1',
                'code' => 'CLS-1',
                'description' => 'First Grade',
                'capacity' => 40,
                'is_active' => true,
            ],
            [
                'name' => '2',
                'code' => 'CLS-2',
                'description' => 'Second Grade',
                'capacity' => 40,
                'is_active' => true,
            ],
            [
                'name' => '3',
                'code' => 'CLS-3',
                'description' => 'Third Grade',
                'capacity' => 40,
                'is_active' => true,
            ],
            [
                'name' => '4',
                'code' => 'CLS-4',
                'description' => 'Fourth Grade',
                'capacity' => 40,
                'is_active' => true,
            ],
            [
                'name' => '5',
                'code' => 'CLS-5',
                'description' => 'Fifth Grade',
                'capacity' => 40,
                'is_active' => true,
            ],
            [
                'name' => '6',
                'code' => 'CLS-6',
                'description' => 'Sixth Grade',
                'capacity' => 40,
                'is_active' => true,
            ],
            [
                'name' => '7',
                'code' => 'CLS-7',
                'description' => 'Seventh Grade',
                'capacity' => 40,
                'is_active' => true,
            ],
            [
                'name' => '8',
                'code' => 'CLS-8',
                'description' => 'Eighth Grade',
                'capacity' => 40,
                'is_active' => true,
            ],
            [
                'name' => '9',
                'code' => 'CLS-9',
                'description' => 'Ninth Grade',
                'capacity' => 35,
                'is_active' => true,
            ],
            [
                'name' => '10',
                'code' => 'CLS-10',
                'description' => 'Tenth Grade',
                'capacity' => 35,
                'is_active' => true,
            ],
        ];

        foreach ($classes as $class) {
            SchoolClass::create($class);
        }

        $this->command->info('Classes seeded successfully!');
    }
}
