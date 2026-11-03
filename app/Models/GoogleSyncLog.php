<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleSyncLog extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'google_sync_logs';

    protected $fillable = [
        'school_id',
        'sync_type',
        'status',
        'summary',
        'details',
        'triggered_by',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}

