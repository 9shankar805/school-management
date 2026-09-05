<?php

namespace App\Http\Controllers\Api\TeacherApp;

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
            AuditLog::record('teacher_login_failed', null, [], ['email' => $request->email]);

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->hasRole('teacher') && ! $user->hasRole('admin')) {
            AuditLog::record('teacher_login_unauthorized', null, [], ['email' => $request->email]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized access. Only teachers can access this app.',
            ], 403);
        }

        // Revoke previous tokens for same device
        $deviceName = $request->input('device_name', 'teacher-app');
        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName)->plainTextToken;

        AuditLog::record('teacher_login', $user);

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
        AuditLog::record('teacher_logout', $request->user());
        $request->user()->currentAccessToken()->delete();

        return response()->json(['status' => 'success', 'message' => 'Logged out successfully.']);
    }

    /**
     * POST /api/auth/forgot-password
     * Sends a password reset link to the teacher's registered email.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return response()->json([
            'status'  => $status === Password::RESET_LINK_SENT ? 'success' : 'error',
            'message' => $status === Password::RESET_LINK_SENT
                ? 'Password reset link sent to your email address.'
                : 'We could not find an account with that email address.',
        ], $status === Password::RESET_LINK_SENT ? 200 : 422);
    }

    /**
     * POST /api/auth/reset-password
     * Resets the password using the token received in the reset email.
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
                event(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );

        return response()->json([
            'status'  => $status === Password::PASSWORD_RESET ? 'success' : 'error',
            'message' => $status === Password::PASSWORD_RESET
                ? 'Password reset successfully. Please log in with your new password.'
                : 'Invalid or expired reset token. Please request a new password reset.',
        ], $status === Password::PASSWORD_RESET ? 200 : 422);
    }

    /**
     * POST /api/auth/google
     */
    public function googleAuth(Request $request): JsonResponse
    {
        return response()->json(['status' => 'pending', 'message' => 'TODO: Implement Google Socialite logic']);
    }

    /**
     * POST /api/auth/microsoft
     */
    public function microsoftAuth(Request $request): JsonResponse
    {
        return response()->json(['status' => 'pending', 'message' => 'TODO: Implement Microsoft Socialite logic']);
    }
}
