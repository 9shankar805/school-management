<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * GET /api/v1/settings
     * Returns all public settings (or all if admin).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Setting::query();

        if (! $request->user()->hasRole('admin')) {
            $query->where('is_public', true);
        }

        $settings = $query->get()->groupBy('group');

        return response()->json(['status' => 'success', 'data' => $settings]);
    }

    /**
     * GET /api/v1/settings/{group}
     */
    public function group(Request $request, string $group): JsonResponse
    {
        $settings = Setting::where('group', $group)->get();

        return response()->json(['status' => 'success', 'data' => $settings]);
    }

    /**
     * POST /api/v1/settings — upsert one or many settings
     * Body: [ { key, value, group?, type? }, ... ] or single object
     */
    public function upsert(Request $request): JsonResponse
    {
        $request->validate([
            '*.key'   => ['required', 'string', 'max:100'],
            '*.value' => ['nullable'],
            '*.group' => ['sometimes', 'string', 'max:60'],
            '*.type'  => ['sometimes', 'in:string,boolean,integer,json,file'],
        ]);

        $items = is_array($request->all()[0] ?? null) ? $request->all() : [$request->all()];

        foreach ($items as $item) {
            Setting::set(
                key: $item['key'],
                value: $item['value'] ?? null,
                group: $item['group'] ?? 'general',
            );
        }

        return response()->json(['status' => 'success', 'message' => 'Settings saved.']);
    }

    /**
     * GET /api/v1/search
     * Global search for students, users, etc.
     */
    public function globalSearch(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $limit = min($request->get('limit', 10), 50);

        \Log::info('Global search called', ['query' => $query, 'user_id' => $request->user()?->id, 'school_id' => $request->user()?->school_id]);

        if (strlen($query) < 2) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        $results = [];
        $schoolId = $request->user()?->school_id ?? null;

        // Search students
        $studentsQuery = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('name', 'student');
        })
        ->where(function ($q) use ($query) {
            $q->where('first_name', 'like', "%{$query}%")
              ->orWhere('last_name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%");
        })
        ->with('academic_info');

        // Filter by school if user is authenticated
        if ($schoolId) {
            $studentsQuery->where('school_id', $schoolId);
        }

        $students = $studentsQuery->limit($limit)->get();

        \Log::info('Students found', ['count' => $students->count(), 'query' => $query, 'school_id' => $schoolId]);

        foreach ($students as $student) {
            $results[] = [
                'type' => 'student',
                'label' => $student->full_name,
                'sublabel' => $student->academic_info->admission_no ?? 'No admission no',
                'href' => "/students/{$student->id}",
                'icon' => 'bi-person',
            ];
        }

        // Search teachers/staff
        $staffQuery = \App\Models\User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['teacher', 'admin', 'principal', 'vice-principal']);
        })
        ->where(function ($q) use ($query) {
            $q->where('first_name', 'like', "%{$query}%")
              ->orWhere('last_name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%");
        });

        // Filter by school if user is authenticated
        if ($schoolId) {
            $staffQuery->where('school_id', $schoolId);
        }

        $staff = $staffQuery->limit($limit)->get();

        \Log::info('Staff found', ['count' => $staff->count(), 'query' => $query, 'school_id' => $schoolId]);

        foreach ($staff as $person) {
            $results[] = [
                'type' => 'staff',
                'label' => $person->full_name,
                'sublabel' => ucfirst(str_replace('-', ' ', $person->primary_role)),
                'href' => "/teachers/view/{$person->id}",
                'icon' => 'bi-person-badge',
            ];
        }

        \Log::info('Search results returned', ['total_results' => count($results)]);

        return response()->json(['status' => 'success', 'data' => $results]);
    }
}
