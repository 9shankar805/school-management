<?php

namespace App\Http\Controllers\Api\DriverApp;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /driver-app/auth/login
     *
     * The driver logs in with the email/password of their linked User account.
     * A Driver record with user_id set is required.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            AuditLog::record('driver_login_failed', null, [], ['email' => $request->email]);

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Find the linked Driver record
        $driver = Driver::where('user_id', $user->id)->first();

        if (! $driver) {
            AuditLog::record('driver_login_no_record', $user);
            return response()->json([
                'status'  => 'error',
                'message' => 'No driver profile is linked to this account. Please contact your school admin.',
            ], 403);
        }

        if ($driver->status !== 'active') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Your driver account is currently inactive. Please contact your school admin.',
            ], 403);
        }

        // Revoke previous tokens for same device
        $deviceName = $request->input('device_name', 'driver-app');
        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName)->plainTextToken;

        AuditLog::record('driver_login', $user);

        // Load the assigned route and vehicle for the app
        $driver->load(['currentVehicle', 'routes' => fn($q) => $q->where('status', 'active')->with('stops')]);

        return response()->json([
            'status'     => 'success',
            'token'      => $token,
            'token_type' => 'Bearer',
            'driver'     => [
                'id'             => $driver->id,
                'name'           => $driver->name,
                'employee_id'    => $driver->employee_id,
                'phone'          => $driver->phone,
                'photo'          => $driver->photo,
                'license_number' => $driver->license_number,
                'vehicle'        => $driver->currentVehicle ? [
                    'id'                  => $driver->currentVehicle->id,
                    'name'                => $driver->currentVehicle->name,
                    'registration_number' => $driver->currentVehicle->registration_number,
                    'capacity'            => $driver->currentVehicle->capacity,
                ] : null,
                'routes' => $driver->routes,
            ],
        ]);
    }

    /**
     * POST /driver-app/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        AuditLog::record('driver_logout', $request->user());
        $request->user()->currentAccessToken()->delete();

        return response()->json(['status' => 'success', 'message' => 'Logged out successfully.']);
    }

    /**
     * GET /driver-app/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user   = $request->user();
        $driver = Driver::where('user_id', $user->id)
            ->with(['currentVehicle', 'routes' => fn($q) => $q->where('status', 'active')])
            ->first();

        if (! $driver) {
            return response()->json(['status' => 'error', 'message' => 'Driver profile not found.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'user_id'       => $user->id,
                'driver_id'     => $driver->id,
                'name'          => $driver->name,
                'employee_id'   => $driver->employee_id,
                'phone'         => $driver->phone,
                'email'         => $driver->email,
                'photo'         => $driver->photo,
                'license_no'    => $driver->license_number,
                'license_expiry'=> $driver->license_expiry?->format('Y-m-d'),
                'status'        => $driver->status,
                'vehicle'       => $driver->currentVehicle,
                'routes'        => $driver->routes,
            ],
        ]);
    }

    /**
     * POST /driver-app/auth/forgot-password
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
}
