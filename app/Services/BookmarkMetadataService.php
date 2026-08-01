<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class BookmarkMetadataService
{
    private const USER_AGENT = 'Mozilla/5.0 (compatible; BookmarkManagerBot/1.0)';

    /**
     * Fetch the title, favicon and description for a given URL.
     *
     * @return array{title: string|null, favicon: string|null, description: string|null}
     */
    public function fetch(string $url): array
    {
        try {
            $response = Http::timeout(8)
                ->connectTimeout(3)
                ->withHeaders(['User-Agent' => self::USER_AGENT])
                ->get($url);
        } catch (ConnectionException) {
            return ['title' => null, 'favicon' => null, 'description' => null];
        }

        if ($response->failed() || $response->body() === '') {
            return ['title' => null, 'favicon' => null, 'description' => null];
        }

        $html = $response->body();

        return [
            'title' => $this->extractTitle($html),
            'favicon' => $this->extractFavicon($url, $html),
            'description' => $this->extractDescription($html),
        ];
    }

    public function extractTitle(string $html): ?string
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches) !== 1) {
            return null;
        }

        return $this->clean($matches[1]);
    }

    public function extractFavicon(string $url, string $html): ?string
    {
        if (preg_match('/<link[^>]+rel=["\']?[^"\']*icon[^"\']*["\']?[^>]*href=["\']([^"\']+)["\']/i', $html, $matches) !== 1) {
            return null;
        }

        $favicon = trim(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5));

        if (str_contains($favicon, '://')) {
            return $favicon;
        }

        $base = $this->baseUrl($url);

        return $base.'/'.ltrim($favicon, '/');
    }

    public function extractDescription(string $html): ?string
    {
        if (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']*)["\']/i', $html, $matches) !== 1
            && preg_match('/<meta[^>]+content=["\']([^"\']*)["\'][^>]+name=["\']description["\']/i', $html, $matches) !== 1) {
            return null;
        }

        return $this->clean($matches[1]);
    }

    private function baseUrl(string $url): string
    {
        $parts = parse_url($url);

        $base = ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? '');

        if (isset($parts['port'])) {
            $base .= ':'.$parts['port'];
        }

        return $base;
    }

    private function clean(string $value): string
    {
        $value = trim(html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5));
        $value = preg_replace('/\s+/u', ' ', $value);

        return $value === '' ? null : $value;
    }
}
