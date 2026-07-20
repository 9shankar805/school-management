<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class OllamaService
{
    /**
     * The base URL for the Ollama instance.
     */
    protected $baseUrl;

    /**
     * The default model to use (phi3:mini or qwen2:1.5b)
     */
    protected $defaultModel;

    public function __construct()
    {
        // By default, Ollama runs on port 11434 locally
        $this->baseUrl = config('services.ollama.url', 'http://localhost:11434');
        $this->defaultModel = config('services.ollama.model', 'qwen2:0.5b');
    }

    /**
     * Generate text from the Ollama model.
     *
     * @param string $prompt
     * @param string|null $model
     * @return string
     * @throws Exception
     */
    public function generate(string $prompt, ?string $model = null): string
    {
        $response = Http::post($this->baseUrl . '/api/generate', [
            'model' => $model ?? $this->defaultModel,
            'prompt' => $prompt,
            'stream' => false, // Set to true if you want to stream the response back
        ]);

        if ($response->successful()) {
            return $response->json('response') ?? '';
        }

        throw new Exception("Ollama API Error: " . $response->body());
    }
}
