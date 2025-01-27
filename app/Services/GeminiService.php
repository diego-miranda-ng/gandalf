<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';
    protected $model = 'gemini-1.5-flash';

    public function getRepositoryContent($owner, $repo)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('GITHUB_TOKEN'),
                'Accept' => 'application/vnd.github.v3+json',
            ])->get("https://api.github.com/repos/{$owner}/{$repo}/contents");


            if ($response->successful()) {
                $files = collect($response->json())
                    ->filter(fn($file) => $file['type'] === 'file')
                    ->map(fn($file) => [
                        'name' => $file['name'],
                        'content' => Http::get($file['download_url'])->body(),
                    ]);
                return $files;
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function generateText(string $prompt, ?string $owner = null, ?string $repo = null)
    {
        try {
            $context = '';
            if ($owner && $repo) {
                $files = $this->getRepositoryContent($owner, $repo);

                if ($files) {
                    $context = "Based on this repository content:\n\n";
                    foreach ($files as $file) {
                        $context .= "File: {$file['name']}\n{$file['content']}\n\n";
                    }
                }
            }

            $fullPrompt = $context . $prompt;

            $response = Http::post("{$this->baseUrl}/models/{$this->model}:generateContent?key=" . env('GOOGLE_API_KEY'), [
                'contents' => [[
                    'parts' => [['text' => $fullPrompt]]
                ]]
            ])->throw()->json();

            return $response['candidates'][0]['content']['parts'][0]['text'] ?? null;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
} 