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
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('class_id')->nullable()->after('id')->constrained('classes')->onDelete('set null');
            $table->foreignId('section_id')->nullable()->after('class_id')->constrained('sections')->onDelete('set null');
            $table->string('email')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->date('date_of_birth')->nullable()->after('phone');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            $table->text('address')->nullable()->after('gender');
            $table->string('guardian_name')->nullable()->after('address');
            $table->string('guardian_phone')->nullable()->after('guardian_name');
            $table->enum('status', ['active', 'inactive', 'graduated'])->default('active')->after('photo');
            
            // Keep old class and section columns for backward compatibility, but make them nullable
            $table->string('class')->nullable()->change();
            $table->string('section')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropForeign(['section_id']);
            $table->dropColumn([
                'class_id', 
                'section_id', 
                'email', 
                'phone', 
                'date_of_birth', 
                'gender', 
                'address', 
                'guardian_name', 
                'guardian_phone', 
                'status'
            ]);
        });
    }
};
