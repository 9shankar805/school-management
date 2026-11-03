<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemImprovement extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'system_improvements';

    protected $fillable = [
        'title',
        'description',
        'category',
        'priority',
        'status',
        'target_date',
        'completed_at',
        'created_by',
        'completed_by',
        'school_id',
        'notes',
    ];

    protected $casts = [
        'target_date'  => 'date',
        'completed_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function scopeRemaining($query)
    {
        return $query->whereIn('status', ['remaining', 'in_progress']);
    }

    public function scopeDone($query)
    {
        return $query->where('status', 'done');
    }

    public function isDone(): bool
    {
        return $this->status === 'done';
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'rose',
            'high'   => 'amber',
            'medium' => 'blue',
            'low'    => 'slate',
            default  => 'slate',
        };
    }
}

