<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class FreeAiService
{
    /**
     * Generate text using the free Pollinations AI API.
     *
     * @param string $prompt
     * @return string
     * @throws Exception
     */
    public function generate(string $prompt): string
    {
        // text.pollinations.ai provides free unlimited ChatGPT-like responses
        $url = 'https://text.pollinations.ai/' . urlencode($prompt);
        
        $response = Http::timeout(30)->get($url);

        if ($response->successful()) {
            return $response->body() ?? '';
        }

        throw new Exception("Free AI API Error: " . $response->status());
    }
}
