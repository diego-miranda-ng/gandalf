<?php

namespace App\Services;

class CacheService
{
    private const OWNER = 'diego-miranda-ng';
    private const REPO = 'gandalf';

    public function __construct(private GitHubService $gitHubService, private GeminiService $geminiService)
    {}

    public function cacheContents(): string
    {
        $cachedContent = $this->geminiService->getCachedContent();
        if (empty($cachedContent['cachedContents'])) {
            $files = $this->gitHubService->getRepositoryContent(self::OWNER, self::REPO);

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
}