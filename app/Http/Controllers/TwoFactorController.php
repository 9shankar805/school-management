<?php

namespace App\Http\Controllers;

use App\Models\TwoFactorAuth;
use App\Services\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function __construct(private TotpService $totp)
    {
        $this->middleware('auth');
    }

    /** GET /two-factor/challenge — show OTP form */
    public function showChallenge(): View
    {
        return view('auth.two-factor-challenge');
    }

    /** POST /two-factor/challenge — verify OTP or recovery code */
    public function verifyChallenge(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $tfa = TwoFactorAuth::where('user_id', auth()->id())->firstOrFail();

        $code = trim($request->code);

        // 1. Try TOTP code
        if (strlen($code) === 6 && ctype_digit($code)) {
            $secret = Crypt::decryptString($tfa->secret);
            if ($this->totp->verify($secret, $code)) {
                $request->session()->put('two_factor_verified', true);
                return redirect()->intended(route('home'));
            }
        }

        // 2. Try recovery code
        if ($tfa->recovery_codes) {
            $hashes = json_decode(Crypt::decryptString($tfa->recovery_codes), true) ?? [];
            foreach ($hashes as $idx => $hash) {
                if (password_verify(strtoupper($code), $hash)) {
                    // Consume this recovery code (one-time use)
                    unset($hashes[$idx]);
                    $tfa->update([
                        'recovery_codes' => Crypt::encryptString(json_encode(array_values($hashes))),
                    ]);
                    $request->session()->put('two_factor_verified', true);
                    return redirect()->intended(route('home'))
                        ->with('warning', 'Recovery code used. ' . count($hashes) . ' remaining.');
                }
            }
        }

        return back()->withErrors(['code' => 'Invalid verification code. Please try again.']);
    }

    /** GET /two-factor/setup — generate secret, show QR code */
    public function showSetup(): View
    {
        $user   = auth()->user();
        $tfa    = TwoFactorAuth::firstOrNew(['user_id' => $user->id]);

        // Generate a fresh secret if not set (or not yet confirmed)
        if (! $tfa->secret || ! $tfa->isConfirmed()) {
            $plainSecret = $this->totp->generateSecret();
            $tfa->fill([
                'method'  => 'totp',
                'secret'  => Crypt::encryptString($plainSecret),
                'enabled' => false,
            ])->save();
        } else {
            $plainSecret = Crypt::decryptString($tfa->secret);
        }

        $qrUrl   = $this->totp->getQrCodeUrl($user->email, $plainSecret);
        $otpauth = $this->totp->getOtpauthUrl($user->email, $plainSecret);

        return view('auth.two-factor-setup', compact('tfa', 'qrUrl', 'otpauth', 'plainSecret'));
    }

    /** POST /two-factor/confirm — verify the scanned code to activate 2FA */
    public function confirmSetup(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/']]);

        $tfa    = TwoFactorAuth::where('user_id', auth()->id())->firstOrFail();
        $secret = Crypt::decryptString($tfa->secret);

        if (! $this->totp->verify($secret, $request->code)) {
            return back()->withErrors(['code' => 'Code verification failed. Ensure your device time is synced.']);
        }

        // Generate and store hashed recovery codes
        $plainCodes   = $this->totp->generateRecoveryCodes();
        $hashedCodes  = array_map(fn($c) => password_hash($c, PASSWORD_BCRYPT), $plainCodes);

        $tfa->update([
            'enabled'        => true,
            'confirmed_at'   => now(),
            'recovery_codes' => Crypt::encryptString(json_encode($hashedCodes)),
        ]);

        $request->session()->put('two_factor_verified', true);

        return redirect()->route('two-factor.recovery-codes')
            ->with('recovery_codes', $plainCodes)
            ->with('status', 'Two-factor authentication enabled successfully.');
    }

    /** GET /two-factor/recovery-codes — show recovery codes once after setup */
    public function recoveryCodes(Request $request): View
    {
        $codes = $request->session()->get('recovery_codes', []);
        return view('auth.two-factor-recovery-codes', compact('codes'));
    }

    /** POST /two-factor/disable — disable 2FA */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);

        TwoFactorAuth::where('user_id', auth()->id())
            ->update(['enabled' => false, 'confirmed_at' => null, 'secret' => null, 'recovery_codes' => null]);

        $request->session()->forget('two_factor_verified');
        return back()->with('status', 'Two-factor authentication has been disabled.');
    }
}
