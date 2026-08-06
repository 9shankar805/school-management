<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Program extends Model
{
    use HasTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name', 'code', 'description', 'level',
        'duration_years', 'department_id', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'program_class', 'program_id', 'class_id');
    }

    public function curriculums()
    {
        return $this->hasMany(Curriculum::class);
    }
}
