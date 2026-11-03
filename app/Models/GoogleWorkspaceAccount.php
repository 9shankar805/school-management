<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleWorkspaceAccount extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'google_workspace_accounts';

    protected $fillable = [
        'school_id',
        'user_id',
        'google_user_id',
        'primary_email',
        'recovery_email',
        'org_unit_path',
        'is_suspended',
        'is_2sv_enrolled',
        'last_login_at',
    ];

    protected $casts = [
        'is_suspended'    => 'boolean',
        'is_2sv_enrolled' => 'boolean',
        'last_login_at'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

