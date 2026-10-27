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
     * Show the application's login form, scoped to the current school tenant if on a subdomain.
     */
    public function showLoginForm(Request $request)
    {
        $school = config('tenant.school') ?? view()->shared('currentSchool');

        // Fallback: check query parameter ?school=... or ?school_id=...
        if (!$school && ($request->has('school') || $request->has('school_id'))) {
            $schoolParam = $request->get('school') ?: $request->get('school_id');
            if (is_numeric($schoolParam)) {
                $school = \App\Models\School::find((int) $schoolParam);
            } else {
                $slug = strtolower(trim((string) $schoolParam));
                $cleanSlug = str_replace(['-', '_', ' '], '', $slug);
                $school = \App\Models\School::where(function ($q) use ($slug, $cleanSlug) {
                    $q->where('slug', $slug)
                      ->orWhereRaw("REPLACE(slug, '-', '') = ?", [$cleanSlug])
                      ->orWhereRaw("LOWER(REPLACE(name, ' ', '')) = ?", [$cleanSlug]);
                })->orderByRaw("CASE WHEN status = 'active' THEN 0 WHEN status = 'pending' THEN 1 ELSE 2 END")->first();
            }

            if ($school) {
                \Illuminate\Support\Facades\Config::set('tenant.school_id', $school->id);
                \Illuminate\Support\Facades\Config::set('tenant.school', $school);
                \Illuminate\Support\Facades\View::share('currentSchool', $school);
            }
        }

        return view('auth.login', compact('school'));
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
