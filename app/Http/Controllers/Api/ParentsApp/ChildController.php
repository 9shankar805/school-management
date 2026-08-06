<?php

namespace App\Http\Controllers\Api\ParentsApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChildController extends Controller
{
    /**
     * GET /api/children
     */
    public function index(Request $request): JsonResponse
    {
        // Get the authenticated parent's children via parent_student pivot
        $children = $request->user()->children()->with('academic_info')->get();

        return response()->json([
            'status' => 'success',
            'data'   => $children->map(function ($child) {
                return [
                    'id'            => $child->id,
                    'first_name'    => $child->first_name,
                    'last_name'     => $child->last_name,
                    'photo'         => $child->photo,
                    'academic_info' => $child->academic_info,
                ];
            })
        ]);
    }

    /**
     * GET /api/children/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        // Validate the child belongs to this parent
        $child = $request->user()->children()->with(['academic_info', 'school'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'            => $child->id,
                'first_name'    => $child->first_name,
                'last_name'     => $child->last_name,
                'email'         => $child->email,
                'phone'         => $child->phone,
                'photo'         => $child->photo,
                'birthday'      => $child->birthday,
                'blood_type'    => $child->blood_type,
                'gender'        => $child->gender,
                'academic_info' => $child->academic_info,
                'school'        => $child->school ? $child->school->name : null,
            ]
        ]);
    }

    /**
     * GET /api/children/{id}/overview
     */
    public function overview(Request $request, $id): JsonResponse
    {
        $child = $request->user()->children()->findOrFail($id);

        // TODO: Calculate real GPA, attendance rate, and rank based on your existing system.
        // For now, return placeholder statistics to unblock frontend.
        return response()->json([
            'status' => 'success',
            'data'   => [
                'child_id'        => $child->id,
                'gpa'             => '3.8',
                'attendance_rate' => '95%',
                'rank'            => '5th',
            ]
        ]);
    }

    /**
     * GET /api/children/{id}/stats
     */
    public function stats(Request $request, $id): JsonResponse
    {
        $child = $request->user()->children()->findOrFail($id);

        // TODO: Aggregate detailed academic statistics (e.g. subject breakdown)
        return response()->json([
            'status' => 'success',
            'data'   => [
                'child_id'             => $child->id,
                'total_assignments'    => 45,
                'completed_assignments'=> 40,
                'upcoming_exams'       => 2,
                'behavior_points'      => 120,
            ]
        ]);
    }
}
