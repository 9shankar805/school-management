<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = ['school_id', 'course_name', 'course_type', 'class_id', 'semester_id', 'session_id', 'position'];

    protected $casts = [
        'position' => 'integer',
    ];

    /**
     * Convenience accessor so ->name works alongside ->course_name
     */
    public function getNameAttribute(): string
    {
        return $this->course_name ?? '';
    }

    public function schoolClass() {
        return $this->belongsTo(\App\Models\SchoolClass::class, 'class_id');
    }

    public function semester() {
        return $this->belongsTo(\App\Models\Semester::class, 'semester_id');
    }

    /**
     * Scope: filter to a specific semester (or null = no filter).
     * Usage: Course::forSemester($semesterId)->where(...)
     */
    public function scopeForSemester($query, $semesterId)
    {
        if ($semesterId && (int) $semesterId > 0) {
            $query->where('semester_id', (int) $semesterId);
        }
        return $query;
    }

    /**
     * Scope: return only unique subjects by course_name per class.
     * Picks the lowest id for each name to get a stable representative row.
     * Usage: Course::uniqueByName()->where('class_id', $id)->get()
     */
    public function scopeUniqueByName($query)
    {
        // Use a subquery to pick the minimum id for each (class_id, course_name) pair
        $query->whereIn('id', function ($sub) {
            $sub->selectRaw('MIN(id)')
                ->from('courses')
                ->whereNull('deleted_at')
                ->groupBy('class_id', 'course_name');
        });
        return $query;
    }

    /**
     * Scope: order by position first, then alphabetically by course_name as fallback.
     * Usage: Course::ordered()->where(...)
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position', 'asc')->orderBy('course_name', 'asc');
    }
}

