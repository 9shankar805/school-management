<?php

namespace App\Traits;

use App\Scopes\TenantScope;

/**
 * HasTenant Trait
 *
 * Drop this onto any Eloquent model that has a `school_id` column.
 * It will:
 *   1. Automatically apply TenantScope to all queries.
 *   2. Auto-fill school_id on create from the current tenant context.
 *
 * Usage:
 *   class Student extends Model {
 *       use HasTenant;
 *   }
 */
trait HasTenant
{
    public static function bootHasTenant(): void
    {
        // Auto-apply the global scope
        static::addGlobalScope(new TenantScope());

        // Auto-fill school_id on creation
        static::creating(function ($model) {
            if (empty($model->school_id)) {
                $schoolId = config('tenant.school_id');
                if ($schoolId) {
                    $model->school_id = $schoolId;
                }
            }
        });
    }

    /**
     * Relationship to the school (tenant).
     */
    public function school()
    {
        return $this->belongsTo(\App\Models\School::class, 'school_id');
    }

    /**
     * Convenience scope — query records for a specific school regardless of
     * the current tenant context. Useful in super-admin cross-school views.
     */
    public function scopeForSchool($query, int $schoolId)
    {
        return $query->withoutGlobalScope(TenantScope::class)
                     ->where($this->getTable() . '.school_id', $schoolId);
    }

    /**
     * Convenience scope — query ALL schools (super-admin only).
     */
    public function scopeAllSchools($query)
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }
}
