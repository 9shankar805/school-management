<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = ['school_id', 'course_name', 'course_type', 'class_id', 'semester_id', 'session_id'];

    /**
     * Convenience accessor so ->name works alongside ->course_name
     */
    public function getNameAttribute(): string
    {
        return $this->course_name ?? '';
    }
}

