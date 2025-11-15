<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'student_id',
        'name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'guardian_name',
        'guardian_phone',
        'class_id',
        'section_id',
        'class', // Keep for backward compatibility
        'section', // Keep for backward compatibility
        'photo',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Get the class that the student belongs to.
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the section that the student belongs to.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    /**
     * Get the attendances for the student.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get class name (from relationship or fallback to old column).
     */
    public function getClassNameAttribute(): ?string
    {
        return $this->schoolClass?->name ?? $this->class;
    }

    /**
     * Get section name (from relationship or fallback to old column).
     */
    public function getSectionNameAttribute(): ?string
    {
        return $this->section?->name ?? $this->section;
    }
}
