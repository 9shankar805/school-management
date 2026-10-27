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

        // ── 1. Subdomain and parameter resolution ────────────────────────
        $host = strtolower($request->getHost());

        // 1a. Explicit query parameter override (e.g. /login?school=genesisglobalschool or ?school_id=2)
        if ($request->has('school') || $request->has('school_id')) {
            $schoolParam = $request->get('school') ?: $request->get('school_id');
            if (is_numeric($schoolParam)) {
                $school = School::find((int) $schoolParam);
            } else {
                $paramSlug = strtolower(trim((string) $schoolParam));
                $cleanParamSlug = str_replace(['-', '_', ' '], '', $paramSlug);
                $school = School::where(function ($q) use ($paramSlug, $cleanParamSlug) {
                    $q->where('slug', $paramSlug)
                      ->orWhereRaw("REPLACE(slug, '-', '') = ?", [$cleanParamSlug])
                      ->orWhereRaw("LOWER(REPLACE(name, ' ', '')) = ?", [$cleanParamSlug]);
                })->orderByRaw("CASE WHEN status = 'active' THEN 0 WHEN status = 'pending' THEN 1 ELSE 2 END")->first();
            }
        }

        // 1b. Subdomain resolution
        if (!$school) {
            $systemSubdomains = ['www', 'app', 'admin', 'super', 'api', 'mail', 'classroom', 'blog', 'support', 'staging', 'demo'];
            $appDomain = config('app.domain', 'nshlms.com');
            $knownDomains = ['nshlms.com', 'nepalschoolhub.com', 'techprocod.com.np', 'localhost', 'test'];
            if ($appDomain && !in_array($appDomain, $knownDomains)) {
                $knownDomains[] = $appDomain;
            }
            $escapedDomains = implode('|', array_map(fn($d) => preg_quote($d, '/'), $knownDomains));

            $extractedSub = null;
            if (preg_match('/^([a-z0-9\-]+)\.(?:' . $escapedDomains . ')$/i', $host, $matches)) {
                $extractedSub = strtolower($matches[1]);
            } elseif (substr_count($host, '.') >= 2) {
                // Generic subdomain fallback: extract first segment
                $parts = explode('.', $host);
                $extractedSub = strtolower($parts[0]);
            }

            if ($extractedSub && !in_array($extractedSub, $systemSubdomains)) {
                $cleanSlug = str_replace(['-', '_', ' '], '', $extractedSub);
                $school = School::where(function ($q) use ($extractedSub, $cleanSlug) {
                    $q->where('slug', $extractedSub)
                      ->orWhereRaw("REPLACE(slug, '-', '') = ?", [$cleanSlug])
                      ->orWhereRaw("LOWER(REPLACE(name, ' ', '')) = ?", [$cleanSlug]);
                })->orderByRaw("CASE WHEN status = 'active' THEN 0 WHEN status = 'pending' THEN 1 ELSE 2 END")->first();
            }
        }

        // 1c. Custom domain / website lookup
        if (!$school && !in_array($host, ['nshlms.com', 'nepalschoolhub.com', 'techprocod.com.np', 'localhost', '127.0.0.1'])) {
            $school = School::where('website', 'LIKE', "%{$host}%")
                ->orderByRaw("CASE WHEN status = 'active' THEN 0 WHEN status = 'pending' THEN 1 ELSE 2 END")
                ->first();
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
