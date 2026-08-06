<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Redirect users whose password has never been changed (or whose account
 * was created with a temporary password) to the password-change page.
 *
 * Set `password_changed_at` to null on new accounts. Once changed, set it.
 * This middleware enforces: "you must set a new password before using the app."
 *
 * Pairs with the `must_change_password` boolean on the users table.
 */
class EnforcePasswordPolicy
{
    private const EXEMPT_ROUTES = [
        'password.change',
        'password.change.update',
        'logout',
        'two-factor.challenge',
        'two-factor.challenge.verify',
    ];

    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Force password change if flagged (e.g. admin-created account)
        if (
            property_exists($user, 'must_change_password') &&
            $user->must_change_password &&
            ! in_array($request->route()?->getName(), self::EXEMPT_ROUTES, true)
        ) {
            return redirect()->route('password.change')
                ->with('warning', 'You must set a new password before continuing.');
        }

        return $next($request);
    }
}
