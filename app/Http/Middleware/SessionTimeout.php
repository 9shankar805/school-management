<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Setting;

/**
 * Enforces a configurable session idle-timeout.
 *
 * The timeout (in minutes) is read from the `settings` table key
 * "session_timeout_minutes", defaulting to the SESSION_LIFETIME env value.
 *
 * On each authenticated request:
 *   1. If the user was last active more than $timeout minutes ago → logout + redirect.
 *   2. Otherwise refresh the last-active timestamp.
 *
 * Register in the 'web' middleware group in Kernel.php.
 */
class SessionTimeout
{
    private const SESSION_KEY  = 'last_activity_at';
    private const SETTINGS_KEY = 'session_timeout_minutes';
    private const CACHE_TTL    = 300; // cache the settings value for 5 min

    /** Routes that skip the timeout check */
    private const EXEMPT_ROUTES = [
        'login', 'logout',
        'password.request', 'password.email', 'password.reset',
        'two-factor.challenge', 'two-factor.challenge.verify',
    ];

    public function handle(Request $request, Closure $next): mixed
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName() ?? '';
        if (in_array($routeName, self::EXEMPT_ROUTES, true)) {
            return $next($request);
        }

        $timeoutMinutes = $this->resolveTimeout();
        $lastActivity   = $request->session()->get(self::SESSION_KEY);

        if ($lastActivity !== null && (time() - $lastActivity) > ($timeoutMinutes * 60)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('warning', "Your session expired after {$timeoutMinutes} minutes of inactivity. Please log in again.");
        }

        // Refresh timestamp on every authenticated request
        $request->session()->put(self::SESSION_KEY, time());

        return $next($request);
    }

    /** Read timeout from settings table (cached), fall back to env value. */
    private function resolveTimeout(): int
    {
        return (int) Cache::remember('setting.' . self::SETTINGS_KEY, self::CACHE_TTL, function () {
            try {
                return Setting::where('key', self::SETTINGS_KEY)->value('value')
                    ?? config('session.lifetime', 120);
            } catch (\Throwable) {
                return config('session.lifetime', 120);
            }
        });
    }
}
