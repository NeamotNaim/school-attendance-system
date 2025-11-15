<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'code',
        'description',
        'capacity',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer',
    ];

    /**
     * Get the sections for the class.
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'class_id');
    }

    /**
     * Get the students for the class.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }
}
