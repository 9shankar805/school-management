<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'title',
        'priority',
        'audience',
        'expiry_date',
        'notice',
        'attachment_path',
        'published_by',
        'session_id'
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function publisher()
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
