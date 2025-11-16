<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
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

        $sectionNames = ['A', 'B', 'C'];

        foreach ($classes as $class) {
            foreach ($sectionNames as $sectionName) {
                Section::create([
                    'class_id' => $class->id,
                    'name' => $sectionName,
                    'code' => $class->code . '-' . $sectionName,
                    'capacity' => 30,
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('Sections seeded successfully!');
    }
}
