<?php

namespace App\Http\Controllers;

use App\Services\FreeAiService;
use Illuminate\Http\Request;

class FreeAiController extends Controller
{
    protected $aiService;

    public function __construct(FreeAiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Test endpoint for the Free AI integration.
     */
    public function chat(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string'
        ]);

        try {
            $response = $this->aiService->generate($request->input('prompt'));

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
