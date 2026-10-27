<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeworkAcknowledgement extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'school_id',
        'homework_id',
        'student_id',
        'parent_id',
        'acknowledged_at',
        'remarks',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
    ];

    public function homework()
    {
        return $this->belongsTo(Homework::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }
}
