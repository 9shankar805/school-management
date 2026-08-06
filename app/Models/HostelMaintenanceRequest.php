<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HostelMaintenanceRequest extends Model
{
    use HasTenant, SoftDeletes;
    protected $fillable = [
        'school_id',
        'hostel_id', 'hostel_room_id', 'reported_by_id', 'issue_type', 'description', 'status', 'priority'
    ];

    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }

    public function room()
    {
        return $this->belongsTo(HostelRoom::class, 'hostel_room_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by_id');
    }
}
