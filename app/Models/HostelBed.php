<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HostelBed extends Model
{
    use HasTenant, SoftDeletes;
    protected $fillable = [
        'school_id',
        'hostel_room_id', 'name', 'status'
    ];

    public function room()
    {
        return $this->belongsTo(HostelRoom::class, 'hostel_room_id');
    }

    public function allocations()
    {
        return $this->hasMany(HostelAllocation::class);
    }
}
