<?php

namespace App\Services;

class UrlNormalizer
{
    /**
     * Normalize a URL so duplicate detection and validation compare like-for-like.
     *
     * Strips whitespace, forces a scheme, lowercases the host, removes default
     * ports, strips tracking parameters and drops the trailing slash.
     */
    public function normalize(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return '';
        }

        if (! str_contains($url, '://')) {
            $url = 'https://'.$url;
        }

        $parts = parse_url($url);

        if ($parts === false || ! isset($parts['host'])) {
            return $url;
        }

        $scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : 'https';
        $host = strtolower($parts['host']);
        $port = isset($parts['port']) ? $this->isDefaultPort($scheme, $parts['port']) ? null : $parts['port'] : null;
        $path = isset($parts['path']) && $parts['path'] !== '' ? $parts['path'] : '/';

        if (str_ends_with($path, '/') && strlen($path) > 1) {
            $path = rtrim($path, '/');
        }

        $query = $this->stripTrackingParameters($parts['query'] ?? null);

        $normalized = $scheme.'://'.$host;

        if ($port !== null) {
            $normalized .= ':'.$port;
        }

        $normalized .= $path;

        if ($query !== null) {
            $normalized .= '?'.$query;
        }

        if (isset($parts['fragment'])) {
            $normalized .= '#'.$parts['fragment'];
        }

        return $normalized;
    }

    /**
     * Return the sha1 hash of the normalized URL, used for duplicate detection.
     */
    public function hash(string $url): string
    {
        return sha1($this->normalize($url));
    }

    private function isDefaultPort(string $scheme, int $port): bool
    {
        return ($scheme === 'http' && $port === 80)
            || ($scheme === 'https' && $port === 443);
    }

    private function stripTrackingParameters(?string $query): ?string
    {
        if ($query === null || $query === '') {
            return null;
        }

        parse_str($query, $params);

        $params = array_filter($params, fn (string|array $value, string|int $key): bool => ! str_starts_with(strtolower((string) $key), 'utm_'), ARRAY_FILTER_USE_BOTH);

        if ($params === []) {
            return null;
        }

        return http_build_query($params);
    }
}
