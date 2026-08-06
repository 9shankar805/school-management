<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FreeAiService
{
    /**
     * Generate text using Groq Llama-3.3-70b-versatile API with fallback.
     *
     * @param string $prompt
     * @return string
     * @throws Exception
     */
    public function generate(string $prompt): string
    {
        $apiKey = config('services.groq.key') ?: env('GROQ_API_KEY', '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');
        $model  = config('services.groq.model') ?: env('GROQ_MODEL', 'llama-3.3-70b-versatile');

        if ($apiKey) {
            try {
                $response = Http::withToken($apiKey)
                    ->timeout(15)
                    ->post('https://api.groq.com/openai/v1/chat/completions', [
                        'model'       => $model,
                        'messages'    => [
                            ['role' => 'user', 'content' => $prompt]
                        ],
                        'temperature' => 0.7,
                    ]);

                if ($response->successful()) {
                    $content = $response->json('choices.0.message.content');
                    if (! empty($content)) {
                        return trim($content);
                    }
                }

                Log::warning('Groq AI generation failed, falling back to backup provider', [
                    'status'   => $response->status(),
                    'response' => $response->json(),
                ]);
            } catch (\Throwable $e) {
                Log::error('Groq AI exception: ' . $e->getMessage());
            }
        }

        // Fallback: text.pollinations.ai
        try {
            $url = 'https://text.pollinations.ai/' . urlencode($prompt);
            $response = Http::timeout(20)->get($url);

            if ($response->successful()) {
                return $response->body() ?? '';
            }
        } catch (\Throwable $e) {
            Log::error('Pollinations AI fallback failed: ' . $e->getMessage());
        }

        throw new Exception("AI Generation failed across all providers.");
    }
}
