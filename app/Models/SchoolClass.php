<?php

namespace App\Models;
use App\Traits\HasTenant;

use App\Models\Course;
use App\Models\Promotion;
use App\Models\Section;
use App\Models\Syllabus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SchoolClass extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = ['school_id', 'class_name', 'session_id'];

    /**
     * Convenience accessor so ->name works alongside ->class_name
     */
    public function getNameAttribute(): string
    {
        return $this->class_name ?? '';
    }

    /**
     * Get the sections for the class.
     */
    public function sections()
    {
        return $this->hasMany(Section::class, 'class_id', 'id');
    }

    /**
     * Get the courses for the class.
     */
    public function courses()
    {
        return $this->hasMany(Course::class, 'class_id', 'id');
    }

    /**
     * Get the syllabi for the class.
     */
    public function syllabi()
    {
        return $this->hasMany(Syllabus::class, 'class_id', 'id');
    }

    /**
     * Get the promotions (student enrollments) for the class.
     */
    public function promotions()
    {
        return $this->hasMany(Promotion::class, 'class_id', 'id');
    }
}
