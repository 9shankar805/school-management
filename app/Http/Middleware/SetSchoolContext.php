<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class SetSchoolContext
{
    /**
     * Attach school_id to the current request context so models can
     * automatically scope queries to the right school (multi-tenancy).
     *
     * Reads school_id from:
     *   1. The authenticated user's academic_info.school_id  (future)
     *   2. X-School-ID header (for super-admin cross-school access)
     *   3. Defaults to null (single-school setup)
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $schoolId = null;

        // Priority 1: explicit header (super-admin only)
        if ($request->hasHeader('X-School-ID') && $request->user()?->hasRole('super-admin')) {
            $schoolId = (int) $request->header('X-School-ID');
        }

        // Priority 2: derive from user's academic/staff record (future use)
        // $schoolId ??= $request->user()?->academic_info?->school_id;

        // Store in config for easy access throughout the request lifecycle
        Config::set('school.current_id', $schoolId);
        $request->merge(['_school_id' => $schoolId]);

        return $next($request);
    }
}
