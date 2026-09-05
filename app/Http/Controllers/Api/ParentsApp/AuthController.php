<?php

namespace App\Http\Controllers\Api\ParentsApp;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            AuditLog::record('parent_login_failed', null, [], ['email' => $request->email]);

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->isParent()) {
            AuditLog::record('parent_login_unauthorized', null, [], ['email' => $request->email]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized access. Only parents can access this app.',
            ], 403);
        }

        // Revoke previous tokens for same device
        $deviceName = $request->input('device_name', 'parents-app');
        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName)->plainTextToken;

        AuditLog::record('parent_login', $user);

        return response()->json([
            'status'  => 'success',
            'token'   => $token,
            'token_type' => 'Bearer',
            'user'    => [
                'id'         => $user->id,
                'name'       => trim($user->first_name . ' ' . $user->last_name),
                'email'      => $user->email,
                'photo'      => $user->photo,
            ],
        ]);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        AuditLog::record('parent_logout', $request->user());
        $request->user()->currentAccessToken()->delete();

        return response()->json(['status' => 'success', 'message' => 'Logged out successfully.']);
    }

    /**
     * POST /api/auth/refresh-token
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->user()->currentAccessToken()->delete();
        $token = $user->createToken('parents-app')->plainTextToken;

        return response()->json(['token' => $token, 'token_type' => 'Bearer']);
    }

    /**
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('parent_info');

        return response()->json([
            'id'          => $user->id,
            'first_name'  => $user->first_name,
            'last_name'   => $user->last_name,
            'email'       => $user->email,
            'phone'       => $user->phone,
            'photo'       => $user->photo,
            'parent_info' => $user->parent_info,
        ]);
    }

    /**
     * PUT /api/auth/profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name'  => ['sometimes', 'string', 'max:255'],
            'phone'      => ['sometimes', 'string', 'max:50'],
        ]);

        $user->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Profile updated successfully.',
            'user'    => $user->only('id', 'first_name', 'last_name', 'phone'),
        ]);
    }

    /**
     * POST /api/auth/forgot-password
     * Sends a password reset link to the parent's email address.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return response()->json([
            'status'  => $status === Password::RESET_LINK_SENT ? 'success' : 'error',
            'message' => $status === Password::RESET_LINK_SENT
                ? 'Password reset link sent to your email.'
                : 'We could not find a user with that email address.',
        ], $status === Password::RESET_LINK_SENT ? 200 : 422);
    }

    /**
     * POST /api/auth/reset-password
     * Resets the password using the token from the email link.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'                 => ['required'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])
                     ->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        return response()->json([
            'status'  => $status === Password::PASSWORD_RESET ? 'success' : 'error',
            'message' => $status === Password::PASSWORD_RESET
                ? 'Password has been reset successfully. Please log in with your new password.'
                : 'The reset token is invalid or has expired. Please request a new reset link.',
        ], $status === Password::PASSWORD_RESET ? 200 : 422);
    }

    /**
     * POST /api/auth/social/google
     */
    public function socialGoogle(Request $request): JsonResponse
    {
        return response()->json(['status' => 'pending', 'message' => 'TODO: Implement Google Socialite logic']);
    }

    /**
     * POST /api/auth/social/microsoft
     */
    public function socialMicrosoft(Request $request): JsonResponse
    {
        return response()->json(['status' => 'pending', 'message' => 'TODO: Implement Microsoft Socialite logic']);
    }
}
