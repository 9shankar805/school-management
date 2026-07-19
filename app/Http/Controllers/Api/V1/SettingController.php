<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
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
}
