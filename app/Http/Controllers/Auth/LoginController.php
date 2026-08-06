<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Post-authentication tenant gate.
     *
     * Super-admins (school_id = null) always pass through.
     * Any other user (school admin, teacher, student, parent, staff, etc.)
     * is only allowed to proceed when their school is `status = active`.
     * Otherwise we log them out immediately and return them to the login
     * page with a clear, school-specific message.
     */
    protected function authenticated(Request $request, $user)
    {
        if ($user->isSuperAdmin()) {
            return null;
        }

        $school = $user->school;

        if (!$school) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')
                ->withErrors(['email' => 'Your account is not linked to any school. Please contact support.']);
        }

        if ($school->status !== 'active') {
            $reason = match ($school->status) {
                'pending'   => 'Your school registration is pending review by a Super Admin. You will receive an email once your school has been approved.',
                'suspended' => 'Your school account has been suspended. Please contact NepalSchoolHub support for assistance.',
                'cancelled' => 'Your school account has been cancelled. Please contact NepalSchoolHub support if you wish to re-activate.',
                default     => 'Your school account is currently inactive. Please contact NepalSchoolHub support.',
            };

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')
                ->withErrors(['email' => $reason]);
        }

        return null;
    }
}
