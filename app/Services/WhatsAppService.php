<?php

namespace App\Services;

use App\Models\School;
use App\Models\WhatsAppMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsAppService — Zavu driver
 * ─────────────────────────────────────────────────────────────────────────────
 * Sends WhatsApp (and falls back to SMS) via the Zavu unified messaging API.
 *
 * API reference: https://crubingllc.mintlify.app
 * Endpoint:      POST https://api.zavu.dev/v1/messages
 * Auth:          Authorization: Bearer <ZAVU_API_KEY>
 *
 * Configure in .env:
 *   ZAVU_API_KEY=zv_live_xxxxxxxxxxxxxxxxxxxx
 *   ZAVU_SENDER_ID=          # optional — your Zavu Sender ID
 *   ZAVU_BASE_URL=https://api.zavu.dev/v1   # override only for staging/proxy
 *
 * Public interface is identical to the previous Meta Cloud API driver, so all
 * callers (SendAbsentParentNotification, CommunicationController, etc.) work
 * without any modification.
 */
class WhatsAppService
{
    // ── Config ────────────────────────────────────────────────────────────────

    private string  $apiKey;
    private string  $baseUrl;
    private ?string $senderId;

    // ── Per-call context (fluent setters, reset after each send) ─────────────

    private ?School $schoolContext = null;
    private ?string $moduleContext = null;

    public function __construct()
    {
        $this->apiKey   = config('services.zavu.api_key',  '');
        $this->baseUrl  = rtrim(config('services.zavu.base_url', 'https://api.zavu.dev/v1'), '/');
        $this->senderId = config('services.zavu.sender_id', '') ?: null;
    }

    // ── Fluent context setters ────────────────────────────────────────────────

    public function setSchool(School $school): static
    {
        $this->schoolContext = $school;
        return $this;
    }

    public function setModule(string $module): static
    {
        $this->moduleContext = $module;
        return $this;
    }

    // ── Public send methods ───────────────────────────────────────────────────

    /**
     * Send a plain-text WhatsApp message.
     *
     * When a WhatsApp Business Account (WABA) is connected in the Zavu
     * dashboard the message is delivered over WhatsApp.  Until then Zavu
     * auto-routes via the next best available channel (SMS).
     *
     * @param  string $to      Recipient phone in E.164 format  (+977xxxxxxxxxx)
     * @param  string $message Message body (plain text, WhatsApp markdown supported)
     */
    public function sendText(string $to, string $message): bool
    {
        if ($this->schoolContext) {
            $message = "[{$this->schoolContext->name}]\n\n{$message}";
        }

        // Do NOT hard-code 'channel' => 'whatsapp' here — let Zavu auto-route.
        // Once a WABA is connected in the Zavu dashboard the sender will
        // automatically deliver via WhatsApp.  Until then it falls back to SMS.
        return $this->dispatch($to, ['text' => $message]);
    }

    /**
     * Send a pre-approved WhatsApp template message.
     *
     * Zavu forwards the template name + components to Meta on your behalf.
     *
     * @param  string $to           Recipient phone in E.164 format
     * @param  string $templateName Approved template name (e.g. "fee_reminder")
     * @param  string $language     BCP-47 language code  (default: "en")
     * @param  array  $components   Meta template component parameters
     */
    public function sendTemplate(
        string $to,
        string $templateName,
        string $language   = 'en',
        array  $components = [],
    ): bool {
        $payload = [
            'channel'  => 'whatsapp',
            'template' => [
                'name'       => $templateName,
                'language'   => $language,
                'components' => $components,
            ],
        ];

        return $this->dispatch($to, $payload);
    }

    /**
     * Send a document/file via a public URL.
     *
     * @param  string $to       Recipient phone in E.164 format
     * @param  string $url      Publicly accessible URL of the file
     * @param  string $caption  Optional caption shown below the document
     * @param  string $filename Filename hint shown in the chat
     */
    public function sendDocument(
        string $to,
        string $url,
        string $caption  = '',
        string $filename = 'document.pdf',
    ): bool {
        return $this->dispatch($to, [
            'channel'  => 'whatsapp',
            'media'    => [
                'url'      => $url,
                'type'     => 'document',
                'caption'  => $caption,
                'filename' => $filename,
            ],
        ]);
    }

    /**
     * Send the same text to multiple recipients.
     * Returns the number of successful sends.
     *
     * @param  string[] $phones   E.164 phone numbers
     * @param  string   $message  Message body
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

    // ── Webhook helpers (kept for CommunicationController compatibility) ──────

    /**
     * Verify a Zavu / Meta webhook GET challenge.
     *
     * Zavu mirrors the Meta hub-challenge handshake, so the same verify token
     * approach works.  Set ZAVU_VERIFY_TOKEN in .env if you use webhooks.
     */
    public function verifyWebhook(string $mode, string $token, string $challenge): string|false
    {
        $verifyToken = config('services.zavu.verify_token', '');

        if ($mode === 'subscribe' && $verifyToken && hash_equals($verifyToken, $token)) {
            return $challenge;
        }

        return false;
    }

    /**
     * Parse an inbound message from a Zavu webhook POST payload.
     *
     * Returns ['from' => string, 'message' => string] or null.
     */
    public function parseWebhook(array $payload): ?array
    {
        try {
            // Zavu webhook shape (mirrors Meta structure for WhatsApp channel)
            $entry   = $payload['entry'][0]                  ?? null;
            $changes = $entry['changes'][0]['value']         ?? null;
            $msgs    = $changes['messages'][0]               ?? null;

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

    // ── Core HTTP dispatch ────────────────────────────────────────────────────

    /**
     * Build the Zavu request body and POST to /messages.
     *
     * Zavu SDK equivalent (TypeScript reference from docs):
     *   const { message } = await zavu.messages.send({ to, text, channel })
     *
     * PHP HTTP equivalent (what this method does):
     *   POST https://api.zavu.dev/v1/messages
     *   Authorization: Bearer <ZAVU_API_KEY>
     *   { "to": "+977…", "text": "…", "channel": "whatsapp" }
     *
     * @param  string $to      E.164 phone number (with leading +)
     * @param  array  $fields  Zavu message fields merged with 'to'
     */
    private function dispatch(string $to, array $fields): bool
    {
        // ── Guard: credentials must be present ────────────────────────────────
        if (! $this->apiKey) {
            Log::warning('WhatsAppService: ZAVU_API_KEY is not set — message not sent.', [
                'to' => $to,
            ]);
            return false;
        }

        // ── Guard: quota check (school-level cap) ─────────────────────────────
        // Only enforce when quota is explicitly tracked (not null) and exhausted.
        if ($this->schoolContext
            && $this->schoolContext->whatsapp_quota !== null
            && $this->schoolContext->whatsapp_quota <= 0) {
            Log::warning('WhatsAppService: quota exhausted for school.', [
                'school_id' => $this->schoolContext->id,
            ]);
            $this->log($to, 'failed', 'Quota exceeded');
            return false;
        }

        // ── Normalise phone to E.164 ──────────────────────────────────────────
        //
        // Numbers stored in the DB can be in several formats:
        //   9805916598     → bare 10-digit Nepali (starts with 98x or 97x)
        //   9779805916598  → 13-digit with country code but no +
        //   +9779805916598 → already E.164 — leave as-is
        //   +9779800000000 → same
        //
        // Strategy:
        //   1. Strip leading whitespace, dashes, spaces.
        //   2. If already starts with '+' → trust it as-is.
        //   3. If 13 digits starting with 977 → prepend '+'.
        //   4. If 10 digits starting with 9[7-8] → Nepali mobile → prepend '+977'.
        //   5. Anything else → prepend '+' and let Zavu validate.
        //
        $raw = preg_replace('/[\s\-()]/', '', $to);   // strip spaces/dashes

        if (str_starts_with($raw, '+')) {
            $phone = $raw;                              // already E.164
        } elseif (strlen($raw) === 13 && str_starts_with($raw, '977')) {
            $phone = '+' . $raw;                        // 977XXXXXXXXXX → +977XXXXXXXXXX
        } elseif (strlen($raw) === 10 && preg_match('/^9[7-8]/', $raw)) {
            $phone = '+977' . $raw;                     // 98XXXXXXXX / 97XXXXXXXX → +97798XXXXXXXX
        } else {
            $phone = '+' . ltrim($raw, '+');            // fallback
        }

        // ── Build request payload ─────────────────────────────────────────────
        // Zavu's messages.send({ to, text }) — 'channel' is optional when a
        // default sender is configured; Zavu auto-routes to the best channel.
        $body = array_merge(['to' => $phone], $fields);

        // Remove sender_id from body — it must be sent as the Zavu-Sender header.
        unset($body['sender_id']);

        // ── HTTP call ─────────────────────────────────────────────────────────
        try {
            $http = Http::withToken($this->apiKey)->timeout(10)
                // Only retry on connection errors / 5xx — never on 4xx (client errors).
                ->retry(2, 300, fn(\Exception $e, $r = null) => !($r && $r->status() < 500));

            // The Zavu-Sender header tells Zavu which sender (phone/WABA) to use.
            if ($this->senderId) {
                $http = $http->withHeaders(['Zavu-Sender' => $this->senderId]);
            }

            $response = $http->post("{$this->baseUrl}/messages", $body);

            if ($response->failed()) {
                $error = $response->json('error.message')
                      ?? $response->json('message')
                      ?? $response->body();

                Log::warning('WhatsAppService: Zavu API error.', [
                    'status' => $response->status(),
                    'error'  => $error,
                    'to'     => $phone,
                ]);
                $this->log($phone, 'failed', (string) $error);
                return false;
            }

            // Zavu returns { "message": { "id": "…", "status": "queued", … } }
            // A present id means the API accepted the request.
            $messageId = $response->json('message.id') ?? $response->json('id');

            if (! $messageId) {
                Log::warning('WhatsAppService: unexpected Zavu response — no message.id.', [
                    'response' => $response->json(),
                    'to'       => $phone,
                ]);
                $this->log($phone, 'failed', 'No message ID in response');
                return false;
            }

            // ── Success ───────────────────────────────────────────────────────
            if ($this->schoolContext) {
                $this->schoolContext->decrement('whatsapp_quota');
            }
            $this->log($phone, 'sent');

            Log::info('WhatsAppService: message queued via Zavu.', [
                'message_id' => $messageId,
                'to'         => $phone,
                'module'     => $this->moduleContext,
            ]);

            return true;

        } catch (\Throwable $e) {
            Log::error('WhatsAppService: HTTP exception.', [
                'error' => $e->getMessage(),
                'to'    => $phone,
            ]);
            $this->log($phone, 'failed', $e->getMessage());
            return false;
        } finally {
            // Always reset per-call context so the same service instance can be
            // reused in the next call without leaking school / module state.
            $this->schoolContext = null;
            $this->moduleContext = null;
        }
    }

    // ── Audit log ─────────────────────────────────────────────────────────────

    /**
     * Write a row to whats_app_messages for school-level quota + audit tracking.
     * Silently skipped when no school context is set (e.g. tests, CLI calls).
     */
    private function log(string $to, string $status, ?string $errorMessage = null): void
    {
        if (! $this->schoolContext) {
            return;
        }

        try {
            WhatsAppMessage::create([
                'school_id'       => $this->schoolContext->id,
                'recipient_phone' => $to,
                'module'          => $this->moduleContext,
                'status'          => $status,
                'error_message'   => $errorMessage,
            ]);
        } catch (\Throwable $e) {
            // Never let a failed audit write crash the caller
            Log::error('WhatsAppService: failed to write audit log.', ['error' => $e->getMessage()]);
        }
    }
}
