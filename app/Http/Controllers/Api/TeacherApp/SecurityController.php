<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SecurityController extends Controller
{
    /**
     * PUT /api/teacher-app/security/change-password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match your current password.']
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['new_password'])
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully.'
        ]);
    }

    /**
     * POST /api/teacher-app/security/two-factor/enable
     */
    public function enableTwoFactor(Request $request): JsonResponse
    {
        // Dummy 2FA enable logic
        return response()->json([
            'status' => 'success',
            'message' => 'Two-factor authentication setup initialized.',
            'qr_code_url' => 'https://example.com/qrcode.png',
            'secret_key' => 'JBSWY3DPEHPK3PXP'
        ]);
    }

    /**
     * POST /api/teacher-app/security/two-factor/verify
     */
    public function verifyTwoFactor(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|size:6'
        ]);

        // Verify logic here

        return response()->json([
            'status' => 'success',
            'message' => 'Two-factor authentication enabled successfully.'
        ]);
    }

    /**
     * POST /api/teacher-app/security/two-factor/disable
     */
    public function disableTwoFactor(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'password' => 'required|string'
        ]);

        $user = $request->user();

        if (!Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.']
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Two-factor authentication disabled successfully.'
        ]);
    }
}
