<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DisciplinaryRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id', 'incident_date', 'severity', 'incident_type',
        'description', 'action_taken', 'parent_notified', 'resolved', 'reported_by',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'resolved'      => 'boolean',
    ];

    const SEVERITIES = ['minor' => 'Minor', 'moderate' => 'Moderate', 'major' => 'Major'];

    const TYPES = [
        'Late arrival', 'Truancy', 'Misconduct', 'Bullying', 'Cheating',
        'Property damage', 'Disrespect', 'Violence', 'Other',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function getSeverityBadgeAttribute(): string
    {
        return match($this->severity) {
            'minor'    => 'bg-amber-100 text-amber-700',
            'moderate' => 'bg-orange-100 text-orange-700',
            'major'    => 'bg-rose-100 text-rose-700',
            default    => 'bg-slate-100 text-slate-600',
        };
    }
}
