<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * RecaptchaService
 *
 * Verifies Google reCAPTCHA v3 / reCAPTCHA Enterprise tokens server-side.
 *
 * Used on:
 *   - Login form (prevent brute force)
 *   - Public admission form
 *   - Contact form
 *   - Any public-facing POST endpoint
 *
 * Config in .env:
 *   RECAPTCHA_SITE_KEY=6Lc5pJ8tAAAAAHoLy8wPwfPKrC9_RUlGxyovuVqd   (public)
 *   RECAPTCHA_SECRET_KEY=6Lc5pJ8tAAAAACFINs_cSw5Hce3KBfveUJ5CqOZi  (secret)
 */
class RecaptchaService
{
    /** Minimum score to accept (0.0 = bot, 1.0 = human). 0.5 is the standard threshold. */
    private const MIN_SCORE = 0.5;

    private string $secretKey;

    public function __construct()
    {
        $this->secretKey = config('services.recaptcha.secret_key', '');
    }

    /**
     * Verify a reCAPTCHA v3 token.
     *
     * @param  string $token      The token from the browser (g-recaptcha-response)
     * @param  string $action     The action name set in JS (e.g. 'login', 'admission')
     * @param  float  $minScore   Override the minimum score (default: 0.5)
     * @return array{success: bool, score: float|null, reason: string}
     */
    public function verify(string $token, string $action = '', float $minScore = self::MIN_SCORE): array
    {
        if (empty($this->secretKey)) {
            // reCAPTCHA not configured — silently pass (dev/staging)
            return ['success' => true, 'score' => null, 'reason' => 'not_configured'];
        }

        if (empty($token)) {
            return ['success' => false, 'score' => null, 'reason' => 'missing_token'];
        }

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => $this->secretKey,
                'response' => $token,
                'remoteip' => request()->ip(),
            ]);

            $result = $response->json();

            if (!$result['success']) {
                $codes = implode(', ', $result['error-codes'] ?? []);
                Log::info('[reCAPTCHA] Verification failed', ['codes' => $codes, 'action' => $action]);
                return ['success' => false, 'score' => null, 'reason' => 'verification_failed:' . $codes];
            }

            $score = (float) ($result['score'] ?? 0);

            // Action mismatch check
            if ($action && isset($result['action']) && $result['action'] !== $action) {
                Log::warning('[reCAPTCHA] Action mismatch', [
                    'expected' => $action,
                    'received' => $result['action'],
                ]);
                return ['success' => false, 'score' => $score, 'reason' => 'action_mismatch'];
            }

            if ($score < $minScore) {
                Log::info('[reCAPTCHA] Score too low', ['score' => $score, 'min' => $minScore]);
                return ['success' => false, 'score' => $score, 'reason' => 'score_too_low'];
            }

            return ['success' => true, 'score' => $score, 'reason' => 'ok'];

        } catch (\Throwable $e) {
            Log::error('[reCAPTCHA] Exception: ' . $e->getMessage());
            // Fail open on network error — don't block legitimate users
            return ['success' => true, 'score' => null, 'reason' => 'network_error_fail_open'];
        }
    }

    /**
     * Quick boolean check — use when you just need pass/fail.
     */
    public function passes(string $token, string $action = ''): bool
    {
        return $this->verify($token, $action)['success'];
    }
}
