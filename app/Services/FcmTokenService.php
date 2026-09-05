<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * FcmTokenService
 *
 * Generates a short-lived OAuth2 access token from the Firebase service account
 * JSON key file for use with the FCM v1 HTTP API.
 *
 * This replaces the legacy FCM Server Key approach — the v1 API is more secure,
 * supports per-device error feedback, and uses standard Google OAuth2 tokens.
 *
 * Key file location: storage/app/firebase-service-account.json
 */
class FcmTokenService
{
    private string $keyFile;

    public function __construct()
    {
        $this->keyFile = storage_path('app/firebase-service-account.json');
    }

    /**
     * Returns a valid OAuth2 access token for the FCM v1 API.
     * Cached for 55 minutes (tokens last 60 min).
     */
    public function getAccessToken(): ?string
    {
        return Cache::remember('fcm_service_account_token', 55 * 60, function () {
            return $this->generateToken();
        });
    }

    /**
     * Force-refresh the cached token (call after token rejection).
     */
    public function refreshToken(): ?string
    {
        Cache::forget('fcm_service_account_token');
        return $this->getAccessToken();
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function generateToken(): ?string
    {
        if (!file_exists($this->keyFile)) {
            Log::warning('[FCM] Service account key file not found: ' . $this->keyFile);
            return null;
        }

        try {
            $key = json_decode(file_get_contents($this->keyFile), true);

            $header  = $this->b64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $now     = time();
            $payload = $this->b64url(json_encode([
                'iss'   => $key['client_email'],
                'sub'   => $key['client_email'],
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
                'scope' => implode(' ', [
                    'https://www.googleapis.com/auth/firebase.messaging',
                    'https://www.googleapis.com/auth/cloud-platform',
                ]),
            ]));

            $signingInput = $header . '.' . $payload;

            // Sign with RSA private key
            $pkeyId = openssl_pkey_get_private($key['private_key']);
            if (!$pkeyId) {
                Log::error('[FCM] Could not load private key from service account.');
                return null;
            }

            $signature = '';
            openssl_sign($signingInput, $signature, $pkeyId, OPENSSL_ALGO_SHA256);

            $jwt = $signingInput . '.' . $this->b64url($signature);

            // Exchange JWT for access token
            $response = $this->httpPost('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            $token = $response['access_token'] ?? null;
            if (!$token) {
                Log::error('[FCM] Token exchange failed', $response);
            }

            return $token;

        } catch (\Throwable $e) {
            Log::error('[FCM] generateToken exception: ' . $e->getMessage());
            return null;
        }
    }

    private function b64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function httpPost(string $url, array $data): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT        => 10,
        ]);
        $body = curl_exec($ch);
        curl_close($ch);
        return json_decode($body, true) ?? [];
    }
}
