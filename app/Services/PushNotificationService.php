<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PushNotificationService
 *
 * Sends push notifications via:
 *   • FCM v1 HTTP API  — uses service account JWT (no legacy server key needed)
 *   • OneSignal        — fallback / alternative provider
 *
 * Configure in .env:
 *   PUSH_PROVIDER=fcm
 *
 *   # FCM v1 — service account key at storage/app/firebase-service-account.json
 *   FCM_PROJECT_ID=nepalschoolhub
 *   FCM_VAPID_PUBLIC_KEY=...        (generated in Firebase Console → Cloud Messaging → Web configuration)
 *   FIREBASE_API_KEY=...            (firebaseConfig for JS SDK)
 *   FIREBASE_AUTH_DOMAIN=...
 *   FIREBASE_STORAGE_BUCKET=...
 *   FCM_SENDER_ID=...
 *   FIREBASE_APP_ID=...
 *
 *   # OneSignal (optional alternative)
 *   ONESIGNAL_APP_ID=...
 *   ONESIGNAL_REST_KEY=...
 */
class PushNotificationService
{
    private string $provider;
    private string $fcmProjectId;
    private FcmTokenService $tokenService;

    public function __construct(FcmTokenService $tokenService)
    {
        $this->tokenService  = $tokenService;
        $this->provider      = config('services.push.provider', 'fcm');
        $this->fcmProjectId  = config('services.fcm.project_id', '');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  HIGH-LEVEL API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Send push to ALL devices registered by a specific user.
     */
    public function sendToUser(int $userId, string $title, string $body, array $data = []): bool
    {
        $tokens = DeviceToken::where('user_id', $userId)
            ->where('provider', $this->provider)
            ->pluck('token')
            ->toArray();

        return !empty($tokens) && $this->sendMultiple($tokens, $title, $body, $data);
    }

    /**
     * Send push to all users with a given Spatie role (optionally scoped to a school).
     */
    public function sendToRole(string $role, string $title, string $body, array $data = [], ?int $schoolId = null): bool
    {
        $query = User::whereHas('roles', fn ($q) => $q->where('name', $role));

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        $userIds = $query->pluck('id');

        $tokens = DeviceToken::whereIn('user_id', $userIds)
            ->where('provider', $this->provider)
            ->pluck('token')
            ->toArray();

        return !empty($tokens) && $this->sendMultiple($tokens, $title, $body, $data);
    }

    /**
     * Send push to a single device token / player ID.
     */
    public function send(string $token, string $title, string $body, array $data = []): bool
    {
        try {
            return match ($this->provider) {
                'fcm'       => $this->sendFcmV1([$token], $title, $body, $data),
                'onesignal' => $this->sendOneSignal([$token], $title, $body, $data),
                default     => false,
            };
        } catch (\Throwable $e) {
            Log::error('PushNotificationService::send failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send to multiple device tokens / player IDs.
     */
    public function sendMultiple(array $tokens, string $title, string $body, array $data = []): bool
    {
        if (empty($tokens)) {
            return false;
        }

        try {
            return match ($this->provider) {
                'fcm'       => $this->sendFcmV1($tokens, $title, $body, $data),
                'onesignal' => $this->sendOneSignal($tokens, $title, $body, $data),
                default     => false,
            };
        } catch (\Throwable $e) {
            Log::error('PushNotificationService::sendMultiple failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Broadcast to a FCM topic (e.g. "students", "teachers").
     */
    public function broadcast(string $topic, string $title, string $body, array $data = []): bool
    {
        try {
            return match ($this->provider) {
                'fcm'       => $this->sendFcmV1Topic($topic, $title, $body, $data),
                'onesignal' => $this->sendOneSignalSegment($topic, $title, $body, $data),
                default     => false,
            };
        } catch (\Throwable $e) {
            Log::error('PushNotificationService::broadcast failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  FCM v1 API  (Service Account OAuth2 — no legacy server key needed)
    //  Endpoint: POST https://fcm.googleapis.com/v1/projects/{id}/messages:send
    // ─────────────────────────────────────────────────────────────────────────

    private function sendFcmV1(array $tokens, string $title, string $body, array $data): bool
    {
        $accessToken = $this->tokenService->getAccessToken();
        if (!$accessToken || !$this->fcmProjectId) {
            Log::warning('[FCM] Service account token or project_id not available.');
            return false;
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->fcmProjectId}/messages:send";
        $success = false;

        // FCM v1 sends one message at a time per token — we batch iterate
        foreach ($tokens as $token) {
            $payload = [
                'message' => [
                    'token'        => $token,
                    'notification' => ['title' => $title, 'body' => $body],
                    'data'         => array_map('strval', $data), // all values must be strings
                    'webpush'      => [
                        'notification' => [
                            'title' => $title,
                            'body'  => $body,
                            'icon'  => '/favicon_io/android-chrome-192x192.png',
                            'badge' => '/favicon_io/favicon-32x32.png',
                            'click_action' => $data['url'] ?? '/',
                        ],
                        'fcm_options' => ['link' => $data['url'] ?? '/'],
                    ],
                ],
            ];

            $response = Http::withToken($accessToken)
                ->post($url, $payload);

            if ($response->successful()) {
                $success = true;
                // Update last_used_at for the token
                DeviceToken::where('token', $token)->update(['last_used_at' => now()]);
            } else {
                $error = $response->json('error.status', '');
                if (in_array($error, ['UNREGISTERED', 'INVALID_ARGUMENT'])) {
                    // Token is stale — remove it
                    DeviceToken::where('token', $token)->delete();
                    Log::info('[FCM] Removed stale token: ' . substr($token, 0, 20) . '...');
                } else {
                    Log::warning('[FCM v1] Send failed for token', [
                        'error'   => $response->json('error'),
                        'token'   => substr($token, 0, 20) . '...',
                    ]);
                }
            }
        }

        return $success;
    }

    private function sendFcmV1Topic(string $topic, string $title, string $body, array $data): bool
    {
        $accessToken = $this->tokenService->getAccessToken();
        if (!$accessToken || !$this->fcmProjectId) {
            return false;
        }

        $response = Http::withToken($accessToken)
            ->post("https://fcm.googleapis.com/v1/projects/{$this->fcmProjectId}/messages:send", [
                'message' => [
                    'topic'        => $topic,
                    'notification' => ['title' => $title, 'body' => $body],
                    'data'         => array_map('strval', $data),
                    'webpush'      => [
                        'notification' => [
                            'title' => $title,
                            'body'  => $body,
                            'icon'  => '/favicon_io/android-chrome-192x192.png',
                        ],
                    ],
                ],
            ]);

        if (!$response->successful()) {
            Log::warning('[FCM v1] Topic send failed', ['topic' => $topic, 'error' => $response->json()]);
        }

        return $response->successful();
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  OneSignal
    // ─────────────────────────────────────────────────────────────────────────

    private function sendOneSignal(array $playerIds, string $title, string $body, array $data): bool
    {
        $appId   = config('services.onesignal.app_id');
        $restKey = config('services.onesignal.rest_key');

        if (!$appId || !$restKey) {
            Log::warning('OneSignal credentials not configured.');
            return false;
        }

        $response = Http::withHeaders([
            'Authorization' => "Basic {$restKey}",
            'Content-Type'  => 'application/json',
        ])->post('https://onesignal.com/api/v1/notifications', [
            'app_id'             => $appId,
            'include_player_ids' => $playerIds,
            'headings'           => ['en' => $title],
            'contents'           => ['en' => $body],
            'data'               => $data,
        ]);

        return $response->ok() && isset($response->json()['id']);
    }

    private function sendOneSignalSegment(string $segment, string $title, string $body, array $data): bool
    {
        $appId   = config('services.onesignal.app_id');
        $restKey = config('services.onesignal.rest_key');

        if (!$appId || !$restKey) {
            return false;
        }

        $response = Http::withHeaders([
            'Authorization' => "Basic {$restKey}",
            'Content-Type'  => 'application/json',
        ])->post('https://onesignal.com/api/v1/notifications', [
            'app_id'            => $appId,
            'included_segments' => [$segment],
            'headings'          => ['en' => $title],
            'contents'          => ['en' => $body],
            'data'              => $data,
        ]);

        return $response->ok();
    }
}
