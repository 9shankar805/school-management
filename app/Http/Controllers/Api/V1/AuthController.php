<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\TwoFactorAuth;
use App\Models\User;
use App\Services\TotpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private TotpService $totp) {}
    /**
     * POST /api/v1/auth/login
     * Returns Sanctum token on success.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            AuditLog::record('login_failed', null, [], ['email' => $request->email]);

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke previous tokens for same device (optional: scope by device_name)
        $deviceName = $request->input('device_name', 'api-client');
        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName)->plainTextToken;

        AuditLog::record('login', $user);

        return response()->json([
            'status'  => 'success',
            'token'   => $token,
            'token_type' => 'Bearer',
            'user'    => [
                'id'         => $user->id,
                'name'       => $user->first_name . ' ' . $user->last_name,
                'email'      => $user->email,
                'roles'      => $user->getRoleNames(),
                'permissions'=> $user->getAllPermissions()->pluck('name'),
                'photo'      => $user->photo,
            ],
            'two_factor_required' => $this->isTwoFactorRequired($user),
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        AuditLog::record('logout', $request->user());
        $request->user()->currentAccessToken()->delete();

        return response()->json(['status' => 'success', 'message' => 'Logged out successfully.']);
    }

    /**
     * GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roles', 'permissions']);

        return response()->json([
            'id'          => $user->id,
            'first_name'  => $user->first_name,
            'last_name'   => $user->last_name,
            'email'       => $user->email,
            'phone'       => $user->phone,
            'photo'       => $user->photo,
            'roles'       => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    /**
     * POST /api/v1/auth/refresh — issue a fresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->user()->currentAccessToken()->delete();
        $token = $user->createToken('api-client')->plainTextToken;

        return response()->json(['token' => $token, 'token_type' => 'Bearer']);
    }

    /**
     * POST /api/v1/auth/forgot-password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return response()->json([
            'status'  => $status === Password::RESET_LINK_SENT ? 'success' : 'error',
            'message' => __($status),
        ]);
    }

    /**
     * POST /api/v1/auth/reset-password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'                 => ['required'],
            'email'                 => ['required', 'email'],
            'password'              => [
                'required',
                'confirmed',
                PasswordRule::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'password_confirmation' => ['required'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        return response()->json([
            'status'  => $status === Password::PASSWORD_RESET ? 'success' : 'error',
            'message' => __($status),
        ]);
    }

    /**
     * POST /api/v1/auth/2fa/enable — generate TOTP secret + QR code URL for the user
     */
    public function enableTwoFactor(Request $request): JsonResponse
    {
        $user = $request->user();

        $tfa = TwoFactorAuth::firstOrNew(['user_id' => $user->id]);

        // Only regenerate if not already confirmed (don't invalidate an active 2FA setup)
        if (! $tfa->exists || ! $tfa->isConfirmed()) {
            $plainSecret = $this->totp->generateSecret();
            $tfa->fill([
                'method'  => 'totp',
                'secret'  => Crypt::encryptString($plainSecret),
                'enabled' => false,
            ])->save();
        } else {
            $plainSecret = Crypt::decryptString($tfa->secret);
        }

        return response()->json([
            'status'   => 'success',
            'secret'   => $plainSecret,
            'qr_url'   => $this->totp->getQrCodeUrl($user->email, $plainSecret),
            'otpauth'  => $this->totp->getOtpauthUrl($user->email, $plainSecret),
            'message'  => 'Scan the QR code with your authenticator app, then call /2fa/verify to confirm.',
        ]);
    }

    /**
     * POST /api/v1/auth/2fa/verify — confirm TOTP setup with first valid code
     */
    public function verifyTwoFactor(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/']]);

        $tfa = TwoFactorAuth::where('user_id', $request->user()->id)->firstOrFail();

        $secret = Crypt::decryptString($tfa->secret);

        if (! $this->totp->verify($secret, $request->code)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid verification code. Ensure your device clock is synced.',
            ], 422);
        }

        // Generate hashed recovery codes
        $plainCodes  = $this->totp->generateRecoveryCodes();
        $hashedCodes = array_map(fn($c) => password_hash($c, PASSWORD_BCRYPT), $plainCodes);

        $tfa->update([
            'enabled'        => true,
            'confirmed_at'   => now(),
            'recovery_codes' => Crypt::encryptString(json_encode($hashedCodes)),
        ]);

        return response()->json([
            'status'         => 'success',
            'message'        => 'Two-factor authentication enabled successfully.',
            'recovery_codes' => $plainCodes,  // shown once — user must store these
        ]);
    }

    /**
     * POST /api/v1/auth/2fa/disable
     */
    public function disableTwoFactor(Request $request): JsonResponse
    {
        $request->validate(['password' => ['required']]);

        if (! Hash::check($request->password, $request->user()->password)) {
            return response()->json(['status' => 'error', 'message' => 'Incorrect password.'], 403);
        }

        TwoFactorAuth::where('user_id', $request->user()->id)
            ->update(['enabled' => false, 'confirmed_at' => null]);

        return response()->json(['status' => 'success', 'message' => '2FA disabled.']);
    }

    // -----------------------------------------------------------------------

    private function isTwoFactorRequired(User $user): bool
    {
        $tfa = TwoFactorAuth::where('user_id', $user->id)->first();
        return $tfa && $tfa->isConfirmed();
    }
}
