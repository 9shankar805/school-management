<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Services\RecaptchaService;
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
     * Validate login request — adds reCAPTCHA v3 check on top of default validation.
     */
    protected function validateLogin(Request $request): void
    {
        $request->validate([
            $this->username() => 'required|string',
            'password'        => 'required|string',
        ]);

        // reCAPTCHA verification (skipped if not configured)
        $token = $request->input('g-recaptcha-response');
        if (config('services.recaptcha.site_key') && $token) {
            $result = app(RecaptchaService::class)->verify($token, 'login');
            if (!$result['success'] && $result['reason'] !== 'not_configured') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'email' => ['Security check failed. Please try again.'],
                ]);
            }
        }
    }

    /**
     * Post-authentication tenant gate.
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
                'pending'   => 'Your school registration is pending review by a Super Admin.',
                'suspended' => 'Your school account has been suspended. Please contact NepalSchoolHub support.',
                'cancelled' => 'Your school account has been cancelled. Please contact NepalSchoolHub support.',
                default     => 'Your school account is currently inactive. Please contact NepalSchoolHub support.',
            };

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors(['email' => $reason]);
        }

        return null;
    }
}
