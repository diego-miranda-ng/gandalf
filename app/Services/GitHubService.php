<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GitHubService
{
    public function getRepositoryContent(string $owner, string $repo, string $path = '')
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('GITHUB_TOKEN'),
            'Accept' => 'application/vnd.github.v3+json',
        ])->get("https://api.github.com/repos/{$owner}/{$repo}/contents/{$path}");


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
    }
}