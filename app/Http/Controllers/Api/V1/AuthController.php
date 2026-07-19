<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\TwoFactorAuth;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
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
            'password'              => ['required', 'min:8', 'confirmed'],
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
     * POST /api/v1/auth/2fa/enable — generate TOTP secret for user
     */
    public function enableTwoFactor(Request $request): JsonResponse
    {
        $user = $request->user();

        $tfa = TwoFactorAuth::firstOrCreate(
            ['user_id' => $user->id],
            ['method' => 'totp', 'enabled' => false]
        );

        // In production: use pragmarx/google2fa to generate real secrets
        $secret = base64_encode(random_bytes(20));
        $tfa->update(['secret' => encrypt($secret)]);

        return response()->json([
            'status' => 'success',
            'secret' => $secret,
            'message' => 'Scan the QR code with your authenticator app, then call /2fa/verify to confirm.',
        ]);
    }

    /**
     * POST /api/v1/auth/2fa/verify — confirm 2FA setup
     */
    public function verifyTwoFactor(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $tfa = TwoFactorAuth::where('user_id', $request->user()->id)->firstOrFail();

        // TODO: validate TOTP code against $tfa->secret (pragmarx/google2fa)
        // Stub: accept any 6-digit code for now
        if (! preg_match('/^\d{6}$/', $request->code)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid code.'], 422);
        }

        $tfa->update(['enabled' => true, 'confirmed_at' => now()]);

        return response()->json(['status' => 'success', 'message' => '2FA enabled.']);
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
