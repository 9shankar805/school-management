<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * PushTokenController
 *
 * Called by the browser JS after the user grants notification permission
 * and receives an FCM registration token.
 *
 * Routes:
 *   POST /push/token        — save / refresh a token
 *   DELETE /push/token      — remove a token (on logout / permission revoked)
 */
class PushTokenController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * POST /push/token
     * Body: { token: string, platform?: string, device_name?: string }
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token'       => 'required|string|max:512',
            'platform'    => 'nullable|string|in:web,android,ios',
            'device_name' => 'nullable|string|max:255',
        ]);

        DeviceToken::register(
            auth()->id(),
            $data['token'],
            [
                'school_id'   => auth()->user()->school_id,
                'provider'    => 'fcm',
                'platform'    => $data['platform']    ?? 'web',
                'device_name' => $data['device_name'] ?? $this->detectDevice($request),
            ]
        );

        return response()->json(['ok' => true]);
    }

    /**
     * DELETE /push/token
     * Body: { token: string }
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['token' => 'required|string|max:512']);

        DeviceToken::where('user_id', auth()->id())
            ->where('token', $request->token)
            ->delete();

        return response()->json(['ok' => true]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function detectDevice(Request $request): string
    {
        $ua = $request->header('User-Agent', '');

        $browser = match (true) {
            str_contains($ua, 'Firefox')         => 'Firefox',
            str_contains($ua, 'Edg')             => 'Edge',
            str_contains($ua, 'Chrome')          => 'Chrome',
            str_contains($ua, 'Safari')          => 'Safari',
            str_contains($ua, 'Opera')           => 'Opera',
            default                              => 'Browser',
        };

        $os = match (true) {
            str_contains($ua, 'Windows')         => 'Windows',
            str_contains($ua, 'Macintosh')       => 'macOS',
            str_contains($ua, 'Android')         => 'Android',
            str_contains($ua, 'iPhone')          => 'iPhone',
            str_contains($ua, 'iPad')            => 'iPad',
            str_contains($ua, 'Linux')           => 'Linux',
            default                              => 'Unknown OS',
        };

        return "{$browser} on {$os}";
    }
}
