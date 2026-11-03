<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolGoogleConfig extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'school_google_configs';

    protected $fillable = [
        'school_id',
        'workspace_domain',
        'admin_email',
        'service_account_json',
        'client_id',
        'is_active',
        'sync_classroom',
        'sync_directory_users',
        'sync_meet_attendance',
        'auto_create_student_emails',
        'connection_status',
        'last_synced_at',
        'last_error_message',
        'stats',
    ];

    protected $casts = [
        'service_account_json'       => 'encrypted',
        'is_active'                  => 'boolean',
        'sync_classroom'             => 'boolean',
        'sync_directory_users'       => 'boolean',
        'sync_meet_attendance'       => 'boolean',
        'auto_create_student_emails' => 'boolean',
        'last_synced_at'             => 'datetime',
        'stats'                      => 'array',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function classroomCourses()
    {
        return $this->hasMany(GoogleClassroomCourse::class, 'school_id', 'school_id');
    }

    public function workspaceAccounts()
    {
        return $this->hasMany(GoogleWorkspaceAccount::class, 'school_id', 'school_id');
    }

    public function syncLogs()
    {
        return $this->hasMany(GoogleSyncLog::class, 'school_id', 'school_id')->latest();
    }

    /**
     * Helper to check if credentials exist and are decodable
     */
    public function hasValidCredentials(): bool
    {
        if (empty($this->service_account_json)) {
            return false;
        }

        try {
            $json = json_decode($this->service_account_json, true);
            return is_array($json) && !empty($json['client_email']) && !empty($json['private_key']);
        } catch (\Throwable $e) {
            return false;
        }
    }
}

