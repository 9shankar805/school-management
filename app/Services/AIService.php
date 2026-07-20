<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    /**
     * Generate text using a local Ollama instance (Free forever).
     * We recommend the 'phi3' model for 8GB RAM servers.
     *
     * @param string $prompt The input prompt
     * @param bool $jsonResponse Whether to enforce a JSON structured response
     * @return string|null The generated text or JSON string, or null on failure
     */
    public function generateText(string $prompt, bool $jsonResponse = false): ?string
    {
        $url = 'http://localhost:11434/api/generate';
        
        $payload = [
            'model' => 'phi3', // Lightweight model perfect for 8GB RAM
            'prompt' => $prompt,
            'stream' => false,
        ];

        if ($jsonResponse) {
            $payload['format'] = 'json';
        }

        try {
            // High timeout because local LLMs can take time on CPU-only machines
            $response = Http::timeout(120)->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['response'])) {
                    return $data['response'];
                }
            }
            
            Log::error('Ollama API Error', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('Ollama API Exception: ' . $e->getMessage());
            return null;
        }
    }
}
