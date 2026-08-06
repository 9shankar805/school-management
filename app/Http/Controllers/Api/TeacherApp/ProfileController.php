<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * GET /api/teacher/profile
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id'          => $user->id,
            'first_name'  => $user->first_name,
            'last_name'   => $user->last_name,
            'email'       => $user->email,
            'phone'       => $user->phone,
            'photo'       => $user->photo,
            'address'     => $user->address,
            'city'        => $user->city,
            'zip'         => $user->zip,
            'blood_type'  => $user->blood_type,
            'religion'    => $user->religion,
            'gender'      => $user->gender,
        ]);
    }

    /**
     * PUT /api/teacher/profile
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name'  => ['sometimes', 'string', 'max:255'],
            'phone'      => ['sometimes', 'string', 'max:50'],
            'address'    => ['sometimes', 'string', 'max:255'],
            'city'       => ['sometimes', 'string', 'max:255'],
        ]);

        $user->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Profile updated successfully.',
            'user'    => $user->only('id', 'first_name', 'last_name', 'phone', 'address', 'city'),
        ]);
    }

    /**
     * PUT /api/teacher/settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        // TODO: Implement settings logic based on user preferences structure
        return response()->json([
            'status'  => 'pending',
            'message' => 'TODO: Implement update settings',
        ]);
    }

    /**
     * GET /api/teacher/achievements
     */
    public function achievements(Request $request): JsonResponse
    {
        // Dummy data for now
        $achievements = [
            ['id' => 1, 'title' => 'Perfect Attendance', 'date' => '2026-08-01'],
            ['id' => 2, 'title' => 'Top Rated Teacher', 'date' => '2026-07-15'],
        ];

        return response()->json([
            'achievements' => $achievements
        ]);
    }

    /**
     * GET /api/teacher/semester-stats
     */
    public function semesterStats(Request $request): JsonResponse
    {
        // Dummy data for now
        $stats = [
            'total_classes_taught' => 120,
            'average_attendance'   => '95%',
            'assignments_graded'   => 450,
        ];

        return response()->json([
            'semester_stats' => $stats
        ]);
    }
}
