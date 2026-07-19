<?php

namespace App\Http\Middleware;

use App\Models\TwoFactorAuth;
use Closure;
use Illuminate\Http\Request;

class EnsureTwoFactorEnabled
{
    /**
     * Redirect users who have 2FA set up but haven't completed the
     * second-factor challenge in this session.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $tfa = TwoFactorAuth::where('user_id', $user->id)->first();

        if ($tfa && $tfa->isConfirmed()) {
            // Check session flag set by 2FA challenge controller
            if (! $request->session()->get('two_factor_verified')) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Two-factor authentication challenge required.',
                        'code'    => 'two_factor_required',
                    ], 423);
                }

                return redirect()->route('two-factor.challenge');
            }
        }

        return $next($request);
    }
}
