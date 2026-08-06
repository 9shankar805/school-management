<?php

namespace App\Http\Controllers\Api\ParentsApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * GET /api/settings/profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'         => $user->id,
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'email'      => $user->email,
                'phone'      => $user->phone,
                'address'    => $user->address,
                'photo'      => $user->photo,
            ],
        ]);
    }

    /**
     * PUT /api/settings/profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'phone'      => 'nullable|string|max:50',
            'address'    => 'nullable|string',
        ]);

        $user->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Profile updated successfully.',
            'data'    => $user,
        ]);
    }

    /**
     * GET /api/settings/preferences
     */
    public function preferences(Request $request): JsonResponse
    {
        // Mock preferences for the app
        return response()->json([
            'status' => 'success',
            'data'   => [
                'dark_mode'          => true,
                'push_notifications' => true,
                'email_notifications'=> false,
                'language'           => 'en',
            ],
        ]);
    }

    /**
     * PUT /api/settings/preferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dark_mode'          => 'nullable|boolean',
            'push_notifications' => 'nullable|boolean',
            'email_notifications'=> 'nullable|boolean',
            'language'           => 'nullable|string|max:10',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Preferences updated (mocked).',
            'data'    => $validated,
        ]);
    }
}
