<?php

namespace App\Http\Middleware;

use App\Models\School;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;

/**
 * SetSchoolContext (Tenant Middleware)
 *
 * Resolves the current school tenant for every request and binds it to:
 *   - config('tenant.school_id')   — used by TenantScope
 *   - config('tenant.school')      — the School model instance
 *   - View::share('currentSchool') — available in all Blade views
 *
 * Resolution priority:
 *   1. Subdomain  — slug.nepalschoolhub.com  → lookup schools.slug
 *   2. Authenticated user's school_id        → direct FK
 *   3. Session    — set during login         → fallback
 *   4. null       — super-admin / platform routes
 */
class SetSchoolContext
{
    public function handle(Request $request, Closure $next): mixed
    {
        $school   = null;
        $schoolId = null;

        // ── 1. Subdomain resolution ──────────────────────────────────────
        $host = $request->getHost();
        // Matches anything like "schoolslug.nepalschoolhub.com"
        if (preg_match('/^([a-z0-9\-]+)\.(nepalschoolhub\.com|localhost)$/i', $host, $matches)) {
            $slug = $matches[1];
            // Ignore www and bare domain
            if (!in_array($slug, ['www', 'app', 'api'])) {
                $school = School::where('slug', $slug)
                                ->where('status', 'active')
                                ->first();
            }
        }

        // ── 2. Authenticated user's school_id ────────────────────────────
        if (!$school && Auth::check()) {
            $user = Auth::user();

            // Super-admins have no school_id — they see everything
            if ($user->isSuperAdmin()) {
                // Allow cross-school override via X-School-ID header (super-admin only)
                if ($request->hasHeader('X-School-ID')) {
                    $overrideId = (int) $request->header('X-School-ID');
                    $school     = School::find($overrideId);
                }
            } elseif (!empty($user->school_id)) {
                $school = School::find($user->school_id);
            }
        }

        // ── 3. Session fallback ───────────────────────────────────────────
        if (!$school && session()->has('tenant_school_id')) {
            $school = School::find(session('tenant_school_id'));
        }

        // ── Bind to config + view ─────────────────────────────────────────
        if ($school) {
            $schoolId = $school->id;
            session(['tenant_school_id' => $schoolId]);
        }

        Config::set('tenant.school_id', $schoolId);
        Config::set('tenant.school',    $school);

        // Share with all Blade views
        View::share('currentSchool', $school);

        return $next($request);
    }
}
