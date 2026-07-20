<?php

namespace App\Http\Controllers;

use App\Services\OllamaService;
use Illuminate\Http\Request;

class OllamaController extends Controller
{
    protected $ollamaService;

    public function __construct(OllamaService $ollamaService)
    {
        $this->ollamaService = $ollamaService;
    }

    /**
     * Test endpoint for the Ollama integration.
     */
    public function chat(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
            'model'  => 'nullable|string', // Allow overriding the default model
        ]);

        try {
            $response = $this->ollamaService->generate(
                $request->input('prompt'),
                $request->input('model')
            );

            return response()->json([
                'success' => true,
                'response' => $response,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
