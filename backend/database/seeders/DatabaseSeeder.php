<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');

        // Create admin user
        $this->command->info('Creating admin user...');
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->command->info('✓ Admin user created (email: admin@example.com, password: password)');

        // Seed classes, sections, students, and holidays
        $this->command->info('');
        $this->call([
            ClassSeeder::class,
            SectionSeeder::class,
            StudentSeeder::class,
            HolidaySeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('🎉 Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('You can now login with:');
        $this->command->info('  Email: admin@example.com');
        $this->command->info('  Password: password');
    }
}
