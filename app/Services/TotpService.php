<?php

namespace App\Services;

/**
 * Pure-PHP TOTP (RFC 6238) implementation — no external SDK required.
 *
 * Compatible with Google Authenticator, Authy, Microsoft Authenticator.
 *
 * Usage:
 *   $service = new TotpService();
 *   $secret  = $service->generateSecret();       // store encrypted
 *   $qrUrl   = $service->getQrCodeUrl($email, $secret);
 *   $valid   = $service->verify($secret, $code);  // true/false
 */
class TotpService
{
    private int $digits    = 6;
    private int $period    = 30;   // seconds per TOTP window
    private int $tolerance = 1;    // ±1 window allowed

    /** Generate a random 20-byte Base32-encoded secret. */
    public function generateSecret(): string
    {
        $bytes = random_bytes(20);
        return $this->base32Encode($bytes);
    }

    /**
     * Generate the current TOTP code for a given secret.
     * Useful for testing.
     */
    public function currentCode(string $secret): string
    {
        return $this->hotp($secret, $this->counter());
    }

    /**
     * Verify a user-supplied code against the secret.
     * Accepts codes from the current window ± $tolerance windows.
     *
     * @param  string $secret  Base32 secret (stored encrypted in DB, pass decrypted here)
     * @param  string $code    6-digit code from authenticator app
     */
    public function verify(string $secret, string $code): bool
    {
        $code    = trim($code);
        $counter = $this->counter();

        for ($i = -$this->tolerance; $i <= $this->tolerance; $i++) {
            if (hash_equals($this->hotp($secret, $counter + $i), $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate 8 one-time recovery codes.
     *
     * @return string[]  Array of plain-text codes (hash before storing)
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(5))); // 10-char hex
        }
        return $codes;
    }

    /**
     * Return a Google Authenticator / Authy-compatible otpauth:// URL.
     * Pass this to a QR code renderer.
     */
    public function getOtpauthUrl(string $label, string $secret, string $issuer = ''): string
    {
        $issuer  = $issuer ?: config('app.name', 'School');
        $encoded = rawurlencode($label);
        $params  = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => $this->digits,
            'period' => $this->period,
        ]);
        return "otpauth://totp/{$encoded}?{$params}";
    }

    /**
     * Return a Google Charts QR code image URL for the otpauth URI.
     * Can replace with a local QR library if Google Charts is not acceptable.
     */
    public function getQrCodeUrl(string $label, string $secret, string $issuer = ''): string
    {
        $otpauth = $this->getOtpauthUrl($label, $secret, $issuer);
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . rawurlencode($otpauth);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /** RFC 4226 HOTP with SHA1 */
    private function hotp(string $secret, int $counter): string
    {
        $key  = $this->base32Decode($secret);
        $data = pack('J', $counter);                // 64-bit big-endian
        $hash = hash_hmac('sha1', $data, $key, true);
        $offset = ord($hash[19]) & 0x0F;
        $code = (
            ((ord($hash[$offset])     & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8)  |
            ( ord($hash[$offset + 3]) & 0xFF)
        ) % (10 ** $this->digits);

        return str_pad((string)$code, $this->digits, '0', STR_PAD_LEFT);
    }

    /** Current TOTP counter (floor of unix-time / period) */
    private function counter(): int
    {
        return (int) floor(time() / $this->period);
    }

    private const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    private function base32Encode(string $bytes): string
    {
        $output  = '';
        $buffer  = 0;
        $bitsLeft = 0;
        foreach (str_split($bytes) as $byte) {
            $buffer   = ($buffer << 8) | ord($byte);
            $bitsLeft += 8;
            while ($bitsLeft >= 5) {
                $bitsLeft -= 5;
                $output   .= self::BASE32_CHARS[($buffer >> $bitsLeft) & 0x1F];
            }
        }
        if ($bitsLeft > 0) {
            $output .= self::BASE32_CHARS[($buffer << (5 - $bitsLeft)) & 0x1F];
        }
        return $output;
    }

    private function base32Decode(string $base32): string
    {
        $base32   = strtoupper($base32);
        $output   = '';
        $buffer   = 0;
        $bitsLeft = 0;
        $map      = array_flip(str_split(self::BASE32_CHARS));
        foreach (str_split($base32) as $char) {
            if (! isset($map[$char])) continue;
            $buffer   = ($buffer << 5) | $map[$char];
            $bitsLeft += 5;
            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output   .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }
        return $output;
    }
}
