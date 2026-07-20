<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hostel extends Model
{
    protected $fillable = [
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
