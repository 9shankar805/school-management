<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppInfoController extends Controller
{
    /**
     * GET /api/teacher-app/app-info/version
     */
    public function version(Request $request): JsonResponse
    {
        return response()->json([
            'version' => '1.0.0',
            'build_number' => 100,
            'force_update' => false,
            'update_url' => 'https://play.google.com/store/apps/details?id=com.schoolmanagement.teacher'
        ]);
    }

    /**
     * GET /api/teacher-app/app-info/privacy-policy
     */
    public function privacyPolicy(Request $request): JsonResponse
    {
        return response()->json([
            'title' => 'Privacy Policy',
            'content' => 'This is the privacy policy content...',
            'url' => url('/privacy-policy')
        ]);
    }

    /**
     * GET /api/teacher-app/app-info/terms
     */
    public function terms(Request $request): JsonResponse
    {
        return response()->json([
            'title' => 'Terms and Conditions',
            'content' => 'These are the terms and conditions...',
            'url' => url('/terms')
        ]);
    }
}
