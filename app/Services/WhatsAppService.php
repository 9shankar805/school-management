<?php

namespace App\Services;

use App\Models\School;
use App\Models\WhatsAppMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Business API service.
 *
 * Uses the official Meta Cloud API (v18+).
 * Configure in .env:
 *   WHATSAPP_PHONE_NUMBER_ID=…
 *   WHATSAPP_ACCESS_TOKEN=EAAxxxx…
 *   WHATSAPP_VERIFY_TOKEN=…   (for webhook verification)
 */
class WhatsAppService
{
    private string $phoneNumberId;
    private string $accessToken;
    private string $baseUrl;
    private ?School $schoolContext = null;
    private ?string $moduleContext = null;

    public function __construct()
    {
        $this->phoneNumberId = config('services.whatsapp.phone_number_id', '');
        $this->accessToken   = config('services.whatsapp.access_token', '');
        $this->baseUrl       = 'https://graph.facebook.com/v18.0';
    }

    public function setSchool(School $school): self
    {
        $this->schoolContext = $school;
        return $this;
    }

    public function setModule(string $module): self
    {
        $this->moduleContext = $module;
        return $this;
    }

    /**
     * Send a plain text message.
     *
     * @param  string $to      Phone number in E.164 format (without leading +)
     * @param  string $message Text body
     */
    public function sendText(string $to, string $message): bool
    {
        if ($this->schoolContext) {
            $message = "*[{$this->schoolContext->name}]*\n\n" . $message;
        }

        return $this->send($to, [
            'type' => 'text',
            'text' => ['preview_url' => false, 'body' => $message],
        ]);
    }

    /**
     * Send a template message (pre-approved WhatsApp template).
     *
     * @param  string $to           Phone number
     * @param  string $templateName Approved template name (e.g. "fee_reminder")
     * @param  string $language     Language code (default: "en")
     * @param  array  $components   Template parameter components
     */
    public function sendTemplate(string $to, string $templateName, string $language = 'en', array $components = []): bool
    {
        $payload = [
            'type'     => 'template',
            'template' => [
                'name'     => $templateName,
                'language' => ['code' => $language],
            ],
        ];

        if (! empty($components)) {
            $payload['template']['components'] = $components;
        }

        return $this->send($to, $payload);
    }

    /**
     * Send a document / file via URL.
     *
     * @param  string $to       Phone number
     * @param  string $url      Public URL of the file
     * @param  string $caption  Optional caption
     * @param  string $filename Filename hint
     */
    public function sendDocument(string $to, string $url, string $caption = '', string $filename = 'document.pdf'): bool
    {
        return $this->send($to, [
            'type'     => 'document',
            'document' => ['link' => $url, 'caption' => $caption, 'filename' => $filename],
        ]);
    }

    /**
     * Send a bulk text message to multiple recipients.
     * Returns count of successful sends.
     */
    public function sendBulk(array $phones, string $message): int
    {
        $sent = 0;
        foreach ($phones as $phone) {
            if ($phone && $this->sendText($phone, $message)) {
                $sent++;
            }
        }
        return $sent;
    }

    /**
     * Verify webhook from Meta (GET challenge).
     */
    public function verifyWebhook(string $mode, string $token, string $challenge): string|false
    {
        $verifyToken = config('services.whatsapp.verify_token', '');
        if ($mode === 'subscribe' && hash_equals($verifyToken, $token)) {
            return $challenge;
        }
        return false;
    }

    /**
     * Parse an incoming webhook event.
     *
     * @return array|null  ['from' => string, 'message' => string] or null
     */
    public function parseWebhook(array $payload): ?array
    {
        try {
            $entry   = $payload['entry'][0] ?? null;
            $changes = $entry['changes'][0]['value'] ?? null;
            $msgs    = $changes['messages'][0] ?? null;

            if (! $msgs || ($msgs['type'] ?? '') !== 'text') {
                return null;
            }

            return [
                'from'    => $msgs['from'],
                'message' => $msgs['text']['body'],
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    // ── Internal ──────────────────────────────────────────────────────────────

    private function send(string $to, array $messagePayload): bool
    {
        if (! $this->phoneNumberId || ! $this->accessToken) {
            Log::warning('WhatsAppService: missing credentials — message not sent.', ['to' => $to]);
            return false;
        }

        // Quota check if school context is set
        if ($this->schoolContext && $this->schoolContext->whatsapp_quota <= 0) {
            Log::warning('WhatsAppService: quota exceeded', ['school' => $this->schoolContext->id]);
            $this->logMessage($to, 'failed', 'Quota exceeded');
            return false;
        }

        try {
            $to = ltrim($to, '+');

            $response = Http::withToken($this->accessToken)
                ->post("{$this->baseUrl}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'recipient_type'    => 'individual',
                    'to'                => $to,
                    ...$messagePayload,
                ]);

            if ($response->failed()) {
                Log::warning('WhatsApp send failed', ['to' => $to, 'response' => $response->json()]);
                $this->logMessage($to, 'failed', json_encode($response->json()));
                return false;
            }

            $success = isset($response->json('messages')[0]['id']);
            if ($success && $this->schoolContext) {
                $this->schoolContext->decrement('whatsapp_quota');
                $this->logMessage($to, 'sent');
            }
            return $success;
        } catch (\Throwable $e) {
            Log::error('WhatsAppService::send exception', ['error' => $e->getMessage()]);
            $this->logMessage($to, 'failed', $e->getMessage());
            return false;
        }
    }

    private function logMessage(string $to, string $status, ?string $errorMessage = null): void
    {
        if (! $this->schoolContext) {
            return; // Only log tenant messages
        }

        WhatsAppMessage::create([
            'school_id'       => $this->schoolContext->id,
            'recipient_phone' => $to,
            'module'          => $this->moduleContext,
            'status'          => $status,
            'error_message'   => $errorMessage,
        ]);
    }
}
