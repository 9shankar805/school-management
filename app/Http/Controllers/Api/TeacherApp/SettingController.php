<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * GET /api/teacher-app/settings
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'settings' => [
                'notifications' => [
                    'email' => true,
                    'push' => true,
                    'sms' => false
                ],
                'language' => 'en',
                'theme' => 'light'
            ]
        ]);
    }

    /**
     * PUT /api/teacher-app/settings/notifications
     */
    public function updateNotifications(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'sometimes|boolean',
            'push' => 'sometimes|boolean',
            'sms' => 'sometimes|boolean',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notification preferences updated successfully.'
        ]);
    }

    /**
     * PUT /api/teacher-app/settings/language
     */
    public function updateLanguage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'language' => 'required|string|in:en,es,fr,de'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Language updated successfully.'
        ]);
    }

    /**
     * PUT /api/teacher-app/settings/theme
     */
    public function updateTheme(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'theme' => 'required|string|in:light,dark,system'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Theme updated successfully.'
        ]);
    }
}
