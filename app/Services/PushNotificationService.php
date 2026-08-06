<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Push Notification service — supports FCM (Firebase Cloud Messaging) and OneSignal.
 *
 * Configure in .env:
 *   PUSH_PROVIDER=fcm      # fcm | onesignal
 *
 *   FCM_SERVER_KEY=AAAA…   (Legacy HTTP API key — use for web + Android)
 *   FCM_PROJECT_ID=…       (optional, for v1 HTTP API)
 *
 *   ONESIGNAL_APP_ID=…
 *   ONESIGNAL_REST_KEY=…
 */
class PushNotificationService
{
    private string $provider;

    public function __construct()
    {
        $this->provider = config('services.push.provider', 'fcm');
    }

    /**
     * Send push notification to a single device token.
     *
     * @param  string $token   FCM registration token or OneSignal player ID
     * @param  string $title   Notification title
     * @param  string $body    Notification body
     * @param  array  $data    Extra key-value data payload
     * @return bool
     */
    public function send(string $token, string $title, string $body, array $data = []): bool
    {
        try {
            return match ($this->provider) {
                'fcm'       => $this->sendFcm([$token], $title, $body, $data),
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
                'fcm'       => $this->sendFcm($tokens, $title, $body, $data),
                'onesignal' => $this->sendOneSignal($tokens, $title, $body, $data),
                default     => false,
            };
        } catch (\Throwable $e) {
            Log::error('PushNotificationService::sendMultiple failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send to all subscribers of a topic/segment (role-based broadcast).
     *
     * FCM:       sends to /topics/{topic}
     * OneSignal: sends to a segment name
     */
    public function broadcast(string $topic, string $title, string $body, array $data = []): bool
    {
        try {
            return match ($this->provider) {
                'fcm'       => $this->sendFcmTopic($topic, $title, $body, $data),
                'onesignal' => $this->sendOneSignalSegment($topic, $title, $body, $data),
                default     => false,
            };
        } catch (\Throwable $e) {
            Log::error('PushNotificationService::broadcast failed', ['topic' => $topic, 'error' => $e->getMessage()]);
            return false;
        }
    }

    // ── FCM (Firebase Cloud Messaging) ────────────────────────────────────────

    private function sendFcm(array $tokens, string $title, string $body, array $data): bool
    {
        $serverKey = config('services.fcm.server_key');
        if (! $serverKey) {
            Log::warning('FCM server key not configured.');
            return false;
        }

        $payload = [
            'registration_ids' => $tokens,
            'notification'     => ['title' => $title, 'body' => $body],
            'data'             => $data,
        ];

        if (count($tokens) === 1) {
            $payload = array_merge($payload, ['to' => $tokens[0]]);
            unset($payload['registration_ids']);
        }

        $response = Http::withHeaders(['Authorization' => "key={$serverKey}"])
            ->post('https://fcm.googleapis.com/fcm/send', $payload);

        if ($response->failed()) {
            Log::warning('FCM push failed', $response->json());
            return false;
        }

        $results = $response->json('results') ?? [];
        return collect($results)->contains(fn($r) => ! isset($r['error']));
    }

    private function sendFcmTopic(string $topic, string $title, string $body, array $data): bool
    {
        $serverKey = config('services.fcm.server_key');
        if (! $serverKey) {
            return false;
        }

        $response = Http::withHeaders(['Authorization' => "key={$serverKey}"])
            ->post('https://fcm.googleapis.com/fcm/send', [
                'to'           => "/topics/{$topic}",
                'notification' => ['title' => $title, 'body' => $body],
                'data'         => $data,
            ]);

        return $response->ok();
    }

    // ── OneSignal ─────────────────────────────────────────────────────────────

    private function sendOneSignal(array $playerIds, string $title, string $body, array $data): bool
    {
        $appId   = config('services.onesignal.app_id');
        $restKey = config('services.onesignal.rest_key');

        if (! $appId || ! $restKey) {
            Log::warning('OneSignal credentials not configured.');
            return false;
        }

        $response = Http::withHeaders([
            'Authorization' => "Basic {$restKey}",
            'Content-Type'  => 'application/json',
        ])->post('https://onesignal.com/api/v1/notifications', [
            'app_id'              => $appId,
            'include_player_ids'  => $playerIds,
            'headings'            => ['en' => $title],
            'contents'            => ['en' => $body],
            'data'                => $data,
        ]);

        return $response->ok() && isset($response->json()['id']);
    }

    private function sendOneSignalSegment(string $segment, string $title, string $body, array $data): bool
    {
        $appId   = config('services.onesignal.app_id');
        $restKey = config('services.onesignal.rest_key');

        if (! $appId || ! $restKey) {
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
