<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use App\Services\CacheService;
use Illuminate\Http\Request;

class GeminiController extends Controller
{

    public function __construct(private GeminiService $geminiService, private CacheService $cacheService)
    {}

    public function prompt(Request $request)
    {
        try {
            $request->validate([
                'prompt' => 'required|string|max:1000',
            ]);

            $cacheName = $this->cacheService->cacheContents();
            $response = $this->geminiService->generateText(
                $request->prompt,
                $cacheName
            );

            return response()->json(['response' => $response]);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
} 