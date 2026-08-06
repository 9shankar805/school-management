<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Hostel extends Model
{
    use HasTenant, SoftDeletes;
    protected $fillable = [
        'school_id',
        'name', 'type', 'address', 'intake_capacity', 'warden_id', 'description'
    ];

    public function warden()
    {
        return $this->belongsTo(User::class, 'warden_id');
    }

    public function rooms()
    {
        return $this->hasMany(HostelRoom::class);
    }

    public function allocations()
    {
        return $this->hasMany(HostelAllocation::class);
    }
}
