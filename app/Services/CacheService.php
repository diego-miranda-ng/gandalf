<?php

namespace App\Services;

class CacheService
{
    private const OWNER = 'diego-miranda-ng';
    private const REPO = 'gandalf';

    private const ALLOWED_PATHS = [
        'app/Services',
        'app/Models',
        'app/Providers',
        'app/Http/Controllers',
        'app/Http/Middleware',
        'app/Http/Requests',
        'app/Http/Resources',
        'app/Http/Controllers',
        'routes',
        'bootstrap',
        'public',
        'config',
    ];

    public function __construct(private GitHubService $gitHubService, private GeminiService $geminiService)
    {}

    public function cacheContents(): string
    {
        $cachedContent = $this->geminiService->getCachedContent();
        if (empty($cachedContent['cachedContents'])) {
            $files = $this->getAllFiles();

            $content = '';
            foreach ($files as $file) {
                $content .= "File: {$file['name']}\n{$file['content']}\n\n";
            }

            $cachedContent = [
                'cachedContents' => [$this->geminiService->cacheContents($content)]
            ];
        }
        
        return $cachedContent['cachedContents'][0]['name'];
    }

    private function getAllFiles(): array
    {
        $files = [];
        collect(self::ALLOWED_PATHS)->each(function ($path) use (&$files) {
            $downloadedFiles = $this->gitHubService->getRepositoryContent(self::OWNER, self::REPO, $path)?->toArray() ?: [];
            $files = array_merge($files, $downloadedFiles);
        });

        return $files;
    }
}
