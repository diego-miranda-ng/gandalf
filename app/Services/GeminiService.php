<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    private const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';
    private const MODEL = 'gemini-1.5-flash-001';

    public function __construct(private GitHubService $gitHubService)
    {}

    public function cacheContents(string $content, string $role = 'user', int $ttlSeconds = 300)
    {
        $response = Http::post(self::BASE_URL . '/cachedContents?key=' . env('GOOGLE_API_KEY'), [
            'model' => "models/" . self::MODEL,
            'contents' => [[
                'parts' => [[
                    'text' => base64_encode($content)
                ]],
                'role' => $role
            ]],
            'systemInstruction' => [
                'parts' => [[
                    'text' => 'Analyze the contents of all the files in this repository. For each file, identify its purpose and functionality. Describe how each file relates to others within the repository, including any dependencies or interactions. Provide the location of each file within the project directory structure. Also, explain the overall context and objective of the project, detailing how the different files and components contribute to the project as a whole.'
                ]]
            ],
            'ttl' => "{$ttlSeconds}s",
        ])->throw()->json();

        return $response;
    }

    public function generateText(string $prompt, ?string $cache = null, string $role = 'user')
    {
        $response = Http::post(self::BASE_URL . '/models/' . self::MODEL . ':generateContent?key=' . env('GOOGLE_API_KEY'), [
            'contents' => [[
                'parts' => [['text' => $prompt]],
                "role" => $role,
            ]],
            "cachedContent" => $cache
        ])->throw()->json();

        return $response;
    }

    public function getCachedContent()
    {
        $response = Http::get(self::BASE_URL . '/cachedContents/?key=' . env('GOOGLE_API_KEY'))
            ->throw()
            ->json();

        return $response;
    }
} 