<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'attendance_recorded', 'student_absent', 'low_attendance', 'report_generated'
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data
            $table->string('icon')->nullable(); // Icon name or emoji
            $table->string('color')->default('blue'); // blue, red, green, yellow
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // null = all users
            $table->timestamps();
            
            $table->index(['user_id', 'is_read']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
