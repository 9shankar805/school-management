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
     * Show the application's login form, scoped to the current school tenant if on a subdomain, query parameter, session, or cookie.
     */
    public function showLoginForm(Request $request)
    {
        // Allow user to explicitly clear school context: /login?school=none or /login?clear_school=1
        if ($request->get('school') === 'none' || $request->has('clear_school')) {
            session()->forget(['tenant_school_id', 'tenant_school_slug']);
            \Illuminate\Support\Facades\Config::set('tenant.school_id', null);
            \Illuminate\Support\Facades\Config::set('tenant.school', null);
            \Illuminate\Support\Facades\View::share('currentSchool', null);

            $response = response()->view('auth.login', ['school' => null]);
            $response->withCookie(cookie()->forget('last_school_slug'));
            $response->withCookie(cookie()->forget('last_school_id'));
            return $response;
        }

        $school = config('tenant.school') ?? view()->shared('currentSchool');

        // Priority 1: check query parameter ?school=... or ?school_id=...
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
        }

        // Priority 2: check session or persistent cookie from last active school/logout
        if (!$school) {
            $schoolId = session('tenant_school_id') ?: $request->cookie('last_school_id');
            $schoolSlug = session('tenant_school_slug') ?: $request->cookie('last_school_slug');

            if ($schoolId && is_numeric($schoolId)) {
                $school = \App\Models\School::find((int) $schoolId);
            } elseif ($schoolSlug) {
                $slug = strtolower(trim((string) $schoolSlug));
                $cleanSlug = str_replace(['-', '_', ' '], '', $slug);
                $school = \App\Models\School::where(function ($q) use ($slug, $cleanSlug) {
                    $q->where('slug', $slug)
                      ->orWhereRaw("REPLACE(slug, '-', '') = ?", [$cleanSlug])
                      ->orWhereRaw("LOWER(REPLACE(name, ' ', '')) = ?", [$cleanSlug]);
                })->orderByRaw("CASE WHEN status = 'active' THEN 0 WHEN status = 'pending' THEN 1 ELSE 2 END")->first();
            }
        }

        if ($school) {
            \Illuminate\Support\Facades\Config::set('tenant.school_id', $school->id);
            \Illuminate\Support\Facades\Config::set('tenant.school', $school);
            \Illuminate\Support\Facades\View::share('currentSchool', $school);
            session([
                'tenant_school_id'   => $school->id,
                'tenant_school_slug' => $school->slug,
            ]);
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

        // Cache active school context in session and cookie for seamless subsequent visits
        if ($school) {
            session([
                'tenant_school_id'   => $school->id,
                'tenant_school_slug' => $school->slug,
            ]);
            cookie()->queue(cookie()->make('last_school_slug', $school->slug, 60 * 24 * 30, null, null, false, false));
            cookie()->queue(cookie()->make('last_school_id', $school->id, 60 * 24 * 30, null, null, false, false));
        }

        return null;
    }

    /**
     * Log the user out of the application and redirect directly into that school's branded login page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // 1. Identify the user's school before destroying the session
        $user = Auth::user();
        $school = null;

        if ($user && $user->school) {
            $school = $user->school;
        } elseif ($user && $user->school_id) {
            $school = \App\Models\School::find($user->school_id);
        }

        if (!$school) {
            $school = config('tenant.school') ?? view()->shared('currentSchool');
        }

        if (!$school && session()->has('tenant_school_id')) {
            $school = \App\Models\School::find(session('tenant_school_id'));
        }

        // 2. Perform guard logout and invalidate session
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 3. Re-persist school context for the login screen
        if ($school && !empty($school->slug)) {
            session([
                'tenant_school_id'   => $school->id,
                'tenant_school_slug' => $school->slug,
            ]);

            $targetUrl = route('login', ['school' => $school->slug]);
            $response = redirect($targetUrl);

            // Set persistent cookies so revisiting /login keeps this school's branding
            $response->withCookie(cookie()->make('last_school_slug', $school->slug, 60 * 24 * 30, null, null, false, false));
            $response->withCookie(cookie()->make('last_school_id', $school->id, 60 * 24 * 30, null, null, false, false));

            return $response;
        }

        return redirect()->route('login');
    }

    /**
     * The user has logged out of the application fallback.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        return redirect()->route('login');
    }
}
