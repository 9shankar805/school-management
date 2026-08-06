<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StaffTraining extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $table = 'staff_training';

    protected $fillable = [
        'school_id',
        'user_id', 'title', 'type', 'provider', 'start_date', 'end_date',
        'duration_hours', 'certificate_path', 'is_completed', 'notes',
    ];

    protected $casts = [
        'start_date'   => 'date',
        'end_date'     => 'date',
        'is_completed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCertificateUrlAttribute(): ?string
    {
        return $this->certificate_path
            ? Storage::url($this->certificate_path)
            : null;
    }
}
