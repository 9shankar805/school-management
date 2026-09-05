<?php

namespace App\Services;

use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SMS Gateway service — supports Twilio, Vonage, Sparrow SMS (Nepal), and a generic HTTP gateway.
 *
 * Configure in .env:
 *   SMS_PROVIDER=twilio          # twilio | vonage | generic | sparrow | broadpay | cellsms | esewa_sms
 *   TWILIO_SID=ACxxxxxxxx
 *   TWILIO_TOKEN=…
 *   TWILIO_FROM=+1234567890
 *
 *   VONAGE_API_KEY=…
 *   VONAGE_API_SECRET=…
 *   VONAGE_FROM=YourSchool
 *
 *   SMS_GENERIC_URL=https://api.example.com/send
 *   SMS_GENERIC_TOKEN=…
 *   SMS_GENERIC_FROM=SchoolName
 *
 *   # Sparrow SMS (Nepal) — also used by broadpay, cellsms, esewa_sms gateways
 *   SPARROW_SMS_TOKEN=eyJ0eXAiOiJKV1Qi…
 *   SPARROW_SMS_FROM=NepalSchool
 *   # or: SPARROW_SMS_SENDER_ID=NepalSchool
 */
class SmsService
{
    private string $provider;

    public function __construct()
    {
        $this->provider = config('services.sms.provider', 'sparrow');
    }

    /**
     * Send a single SMS.
     *
     * @param  string $to      Phone number in E.164 format (+1234567890)
     * @param  string $message Message body (max 160 chars for single SMS)
     * @return bool  true on success
     */
    public function send(string $to, string $message): bool
    {
        try {
            return match ($this->provider) {
                'twilio'    => $this->sendViaTwilio($to, $message),
                'vonage'    => $this->sendViaVonage($to, $message),
                'generic'   => $this->sendViaGeneric($to, $message),
                'sparrow'   => $this->sendViaSparrow($to, $message),
                'broadpay'  => $this->sendViaSparrow($to, $message),
                'cellsms'   => $this->sendViaSparrow($to, $message),
                'esewa_sms' => $this->sendViaSparrow($to, $message),
                default     => throw new \InvalidArgumentException("Unknown SMS provider: {$this->provider}"),
            };
        } catch (\Throwable $e) {
            Log::error('SmsService::send failed', [
                'provider' => $this->provider,
                'to'       => $to,
                'error'    => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send SMS to multiple recipients.
     * Returns count of successful sends.
     */
    public function sendBulk(array $recipients, string $message): int
    {
        $sent = 0;
        foreach ($recipients as $phone) {
            if ($phone && $this->send($phone, $message)) {
                $sent++;
            }
        }
        return $sent;
    }

    // ── Twilio ────────────────────────────────────────────────────────────────

    private function sendViaTwilio(string $to, string $message): bool
    {
        $sid   = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from  = config('services.twilio.from');

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'To'   => $to,
                'From' => $from,
                'Body' => $message,
            ]);

        if ($response->failed()) {
            Log::warning('Twilio SMS failed', $response->json());
            return false;
        }

        return in_array($response->json('status'), ['queued', 'sent', 'delivered'], true);
    }

    // ── Vonage (Nexmo) ────────────────────────────────────────────────────────

    private function sendViaVonage(string $to, string $message): bool
    {
        $response = Http::post('https://rest.nexmo.com/sms/json', [
            'api_key'    => config('services.vonage.api_key'),
            'api_secret' => config('services.vonage.api_secret'),
            'to'         => ltrim($to, '+'),
            'from'       => config('services.vonage.from', 'School'),
            'text'       => $message,
        ]);

        $messages = $response->json('messages') ?? [];
        return ($messages[0]['status'] ?? '1') === '0';
    }

    // ── Generic HTTP Gateway ──────────────────────────────────────────────────

    private function sendViaGeneric(string $to, string $message): bool
    {
        $url   = config('services.sms.generic_url');
        $token = config('services.sms.generic_token');
        $from  = config('services.sms.generic_from', config('app.name'));

        $response = Http::withToken($token)
            ->post($url, compact('to', 'from', 'message'));

        return $response->successful();
    }

    // ── Sparrow SMS (Nepal) — also used by broadpay, cellsms, esewa_sms ──────

    private function sendViaSparrow(string $to, string $message): bool
    {
        try {
            $token = config('services.sms.sparrow_token') ?? config('services.sparrow.token');
            $from  = config('services.sms.sparrow_from') ?? config('services.sparrow.from') ?? config('services.sms.sparrow_sender_id') ?? config('services.sparrow.sender_id') ?? config('app.name') ?? 'NepalSchoolHub';

            $to = trim($to);
            if (strlen($to) === 10 && str_starts_with($to, '9')) {
                $to = '+977' . $to;
            }

            $response = Http::timeout(15)->get('https://api.sparrowsms.com/v2/sms/', [
                'token' => $token,
                'from'  => $from,
                'to'    => $to,
                'text'  => $message,
            ]);

            $json = $response->json();
            $statusCode = $json['status_code'] ?? null;
            $responseStatus = $json['response']['status'] ?? null;
            $topLevelStatus = $json['status'] ?? null;

            $ok = ($statusCode === 200) || ($topLevelStatus === 'success') || ($responseStatus === 'success');

            if (!$ok) {
                Log::warning('Sparrow SMS failed', ['response' => $json]);
            }

            SmsLog::create([
                'school_id'       => config('tenant.school_id'),
                'sender_id'       => auth()->id() ?? null,
                'recipient_phone' => $to,
                'gateway'         => 'sparrow',
                'type'            => 'alert',
                'message'         => $message,
                'status'          => $ok ? 'sent' : 'failed',
                'error_message'   => !$ok ? json_encode($json) : null,
                'sent_at'         => now(),
            ]);

            return $ok;
        } catch (\Throwable $e) {
            Log::error('Sparrow SMS exception', [
                'to'      => $to,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}
