<?php

namespace App\Models;
use App\Traits\HasTenant;

use App\Models\Course;
use App\Models\Promotion;
use App\Models\Section;
use App\Models\Syllabus;
use App\Models\AssignedTeacher;
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
        return $this->hasMany(Course::class, 'class_id', 'id')
                    ->whereIn('id', function($sub) {
                        $sub->selectRaw('MIN(id)')
                            ->from('courses')
                            ->whereNull('deleted_at')
                            ->groupBy('class_id', 'course_name');
                    });
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
     * Excludes promotions whose section_id points to a soft-deleted section
     * so that student counts never include students from deleted sections.
     */
    public function promotions()
    {
        return $this->hasMany(Promotion::class, 'class_id', 'id')
                    ->where(function ($q) {
                        // Keep promotions with no section (section_id is null)
                        // or whose section still exists (not soft-deleted)
                        $q->whereNull('section_id')
                          ->orWhereExists(function ($sub) {
                              $sub->selectRaw('1')
                                  ->from('sections')
                                  ->whereColumn('sections.id', 'promotions.section_id')
                                  ->whereNull('sections.deleted_at');
                          });
                    });
    }

    /**
     * Get the assigned teachers (subject / class-teacher assignments) for the class.
     */
    public function assignedTeachers()
    {
        return $this->hasMany(AssignedTeacher::class, 'class_id', 'id');
    }
}
