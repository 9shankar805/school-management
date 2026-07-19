<?php

namespace App\Http\Controllers;

use App\Models\TwoFactorAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * GET /two-factor/challenge
     * Show the 2FA challenge form (after login, before accessing the app).
     */
    public function showChallenge(): View
    {
        return view('auth.two-factor-challenge');
    }

    /**
     * POST /two-factor/challenge
     * Verify the one-time code and unlock the session.
     */
    public function verifyChallenge(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $tfa = TwoFactorAuth::where('user_id', auth()->id())->firstOrFail();

        // TODO: verify TOTP code against decrypt($tfa->secret) using pragmarx/google2fa
        // Stub: accept any 6-digit code during development
        if (! preg_match('/^\d{6}$/', $request->code)) {
            return back()->withErrors(['code' => 'Invalid verification code. Please try again.']);
        }

        $request->session()->put('two_factor_verified', true);

        return redirect()->intended(route('home'));
    }

    /**
     * GET /two-factor/setup
     * Show 2FA setup / QR code page.
     */
    public function showSetup(): View
    {
        $user = auth()->user();
        $tfa  = TwoFactorAuth::firstOrCreate(
            ['user_id' => $user->id],
            ['method' => 'totp', 'enabled' => false]
        );

        return view('auth.two-factor-setup', compact('tfa'));
    }

    /**
     * POST /two-factor/disable
     * Disable 2FA after password confirmation.
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);

        TwoFactorAuth::where('user_id', auth()->id())
            ->update(['enabled' => false, 'confirmed_at' => null]);

        $request->session()->forget('two_factor_verified');

        return back()->with('status', '2FA has been disabled.');
    }
}
