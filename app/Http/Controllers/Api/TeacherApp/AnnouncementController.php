<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * GET /api/teacher-app/announcements
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'announcements' => [
                [
                    'id' => 1,
                    'title' => 'School Closure Tomorrow',
                    'snippet' => 'Due to heavy snow, the school will be...',
                    'type' => 'global',
                    'created_at' => '2026-08-04T07:00:00Z'
                ],
                [
                    'id' => 2,
                    'title' => 'Science Fair Registration',
                    'snippet' => 'Reminder to all teachers to register their...',
                    'type' => 'staff',
                    'created_at' => '2026-08-03T14:00:00Z'
                ]
            ]
        ]);
    }

    /**
     * POST /api/teacher-app/announcements
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'target_audience' => 'required|in:students,parents,all',
            'class_id' => 'nullable|integer'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Announcement posted successfully.',
            'announcement' => array_merge(['id' => rand(10, 100), 'type' => 'class'], $validated)
        ]);
    }

    /**
     * GET /api/teacher-app/announcements/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        return response()->json([
            'announcement' => [
                'id' => $id,
                'title' => 'School Closure Tomorrow',
                'body' => 'Due to heavy snow, the school will be closed tomorrow. All classes are suspended.',
                'type' => 'global',
                'created_at' => '2026-08-04T07:00:00Z',
                'author' => 'Principal Office'
            ]
        ]);
    }
}
