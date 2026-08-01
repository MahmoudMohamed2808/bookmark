<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class UrlValidationService
{
    public function __construct(private readonly UrlNormalizer $normalizer) {}

    /**
     * Validate that a URL is well-formed.
     */
    public function isValid(string $url): bool
    {
        $normalized = $this->normalizer->normalize($url);

        return $normalized !== '' && filter_var($normalized, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate that a URL is reachable over HTTP(S).
     */
    public function isReachable(string $url): bool
    {
        if (! $this->isValid($url)) {
            return false;
        }

        try {
            $response = Http::timeout(5)
                ->connectTimeout(3)
                ->head($url);
        } catch (ConnectionException) {
            return false;
        }

        return $response->successful() || $response->redirect();
    }
}
