<?php

namespace App\Http\Controllers\Api\StudentApp;

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
     * POST /student-app/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            AuditLog::record('student_login_failed', null, [], ['email' => $request->email]);

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->isStudent()) {
            AuditLog::record('student_login_unauthorized', null, [], ['email' => $request->email]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized access. Only students can access this app.',
            ], 403);
        }

        // Revoke previous tokens for same device
        $deviceName = $request->input('device_name', 'student-app');
        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName)->plainTextToken;

        AuditLog::record('student_login', $user);

        return response()->json([
            'status'     => 'success',
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => [
                'id'           => $user->id,
                'name'         => trim($user->first_name . ' ' . $user->last_name),
                'email'        => $user->email,
                'photo'        => $user->photo,
                'admission_no' => $user->admission_no,
                'roll_no'      => $user->roll_no,
            ],
        ]);
    }

    /**
     * POST /student-app/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        AuditLog::record('student_logout', $request->user());
        $request->user()->currentAccessToken()->delete();

        return response()->json(['status' => 'success', 'message' => 'Logged out successfully.']);
    }

    /**
     * POST /student-app/auth/refresh-token
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->user()->currentAccessToken()->delete();
        $token = $user->createToken('student-app')->plainTextToken;

        return response()->json(['token' => $token, 'token_type' => 'Bearer']);
    }

    /**
     * GET /student-app/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['academic_info', 'currentStatus.schoolClass']);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'           => $user->id,
                'first_name'   => $user->first_name,
                'last_name'    => $user->last_name,
                'email'        => $user->email,
                'phone'        => $user->phone,
                'photo'        => $user->avatar,
                'admission_no' => $user->admission_no,
                'roll_no'      => $user->roll_no,
                'gender'       => $user->gender,
                'birthday'     => $user->birthday?->format('Y-m-d'),
                'class'        => $user->currentStatus?->schoolClass?->class_name,
                'section'      => null,
                'academic_info' => $user->academic_info,
            ],
        ]);
    }

    /**
     * PUT /student-app/auth/profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'phone' => ['sometimes', 'string', 'max:50'],
        ]);

        $user->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Profile updated successfully.',
            'user'    => $user->only('id', 'first_name', 'last_name', 'phone'),
        ]);
    }

    /**
     * POST /student-app/auth/forgot-password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return response()->json([
            'status'  => $status === Password::RESET_LINK_SENT ? 'success' : 'error',
            'message' => $status === Password::RESET_LINK_SENT
                ? 'Password reset link sent to your email.'
                : 'We could not find an account with that email address.',
        ], $status === Password::RESET_LINK_SENT ? 200 : 422);
    }

    /**
     * POST /student-app/auth/reset-password
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
                ? 'Password reset successfully. Please log in with your new password.'
                : 'Invalid or expired reset token. Please request a new password reset.',
        ], $status === Password::PASSWORD_RESET ? 200 : 422);
    }
}
