<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->string('name'); // e.g., "A", "B", "Science", "Arts"
            $table->string('code')->unique(); // e.g., "C1-A", "C1-B"
            $table->integer('capacity')->default(30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['class_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
