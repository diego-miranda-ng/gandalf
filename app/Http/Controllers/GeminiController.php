<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use Illuminate\Http\Request;

class GeminiController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function generate(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'owner' => 'nullable|string',
            'repo' => 'nullable|string'
        ]);

        $response = $this->geminiService->generateText(
            $request->prompt,
            $request->owner,
            $request->repo
        );

        return response()->json(['response' => $response]);
    }
} 