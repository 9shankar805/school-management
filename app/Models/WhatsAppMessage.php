<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppMessage extends Model
{
    protected $fillable = [
        'school_id',
        'recipient_phone',
        'module',
        'status',
        'error_message',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
