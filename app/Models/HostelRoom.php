<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HostelRoom extends Model
{
    use HasTenant, SoftDeletes;
    protected $fillable = [
        'school_id',
        'hostel_id', 'room_number', 'room_type', 'capacity', 'cost_per_bed', 'description'
    ];

    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }

    public function beds()
    {
        return $this->hasMany(HostelBed::class);
    }

    public function allocations()
    {
        return $this->hasMany(HostelAllocation::class);
    }
}
