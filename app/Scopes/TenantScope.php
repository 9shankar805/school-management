<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * TenantScope — automatically constrains queries to the current school tenant.
 *
 * Applied as a global scope on all models that have a `school_id` column.
 * The current school_id is resolved from the request cycle by TenantMiddleware
 * and stored in config('tenant.school_id').
 *
 * Super-admins bypass this scope entirely (they can see all tenants).
 * Use ->withoutGlobalScope(TenantScope::class) for cross-tenant queries.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $schoolId = config('tenant.school_id');

        // No school context (super-admin or not yet resolved) → no constraint
        if (empty($schoolId)) {
            return;
        }

        $builder->where($model->getTable() . '.school_id', $schoolId);
    }
}
