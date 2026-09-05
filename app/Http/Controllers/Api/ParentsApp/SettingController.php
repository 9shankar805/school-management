<?php

namespace App\Http\Controllers\Api\ParentsApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * GET /api/settings
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'profile' => [
                    'id'         => $user->id,
                    'first_name' => $user->first_name,
                    'last_name'  => $user->last_name,
                    'email'      => $user->email,
                    'phone'      => $user->phone,
                    'address'    => $user->address,
                    'photo'      => $user->photo,
                ],
                'preferences' => [
                    'dark_mode'          => true,
                    'theme'              => 'system',
                    'language'           => 'en',
                    'push_notifications' => true,
                    'email_notifications'=> false,
                    'sms_notifications'  => true,
                ],
                'privacy' => [
                    'share_data'         => false,
                    'show_online_status' => true,
                ],
            ],
        ]);
    }

    /**
     * PUT /api/settings
     */
    public function update(Request $request): JsonResponse
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
            'message' => 'Settings updated successfully.',
            'data'    => $user,
        ]);
    }

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
        return $this->update($request);
    }

    /**
     * GET /api/settings/preferences
     */
    public function preferences(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data'   => [
                'dark_mode'          => true,
                'theme'              => 'system',
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
            'theme'              => 'nullable|string',
            'push_notifications' => 'nullable|boolean',
            'email_notifications'=> 'nullable|boolean',
            'language'           => 'nullable|string|max:10',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Preferences updated.',
            'data'    => $validated,
        ]);
    }

    /**
     * PUT /api/settings/notifications
     */
    public function updateNotifications(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'push_notifications'  => 'nullable|boolean',
            'email_notifications' => 'nullable|boolean',
            'sms_notifications'   => 'nullable|boolean',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Notification preferences updated.',
            'data'    => $validated,
        ]);
    }

    /**
     * PUT /api/settings/theme
     */
    public function updateTheme(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'theme' => 'required|string|in:light,dark,system',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Theme preference updated.',
            'data'    => $validated,
        ]);
    }

    /**
     * PUT /api/settings/language
     */
    public function updateLanguage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'language' => 'required|string|max:10',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Language updated.',
            'data'    => $validated,
        ]);
    }

    /**
     * GET /api/settings/privacy
     */
    public function privacy(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data'   => [
                'share_data'         => false,
                'show_online_status' => true,
            ],
        ]);
    }

    /**
     * PUT /api/settings/privacy
     */
    public function updatePrivacy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'share_data'         => 'nullable|boolean',
            'show_online_status' => 'nullable|boolean',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Privacy settings updated.',
            'data'    => $validated,
        ]);
    }
}
