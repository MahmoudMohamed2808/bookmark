<?php

namespace App\Services;

use App\Models\Bookmark;

class DuplicateDetectionService
{
    public function __construct(private readonly UrlNormalizer $normalizer) {}

    /**
     * Find an existing bookmark with the same normalized URL for the user.
     */
    public function findDuplicate(string $url, int $userId): ?Bookmark
    {
        return Bookmark::query()
            ->where('user_id', $userId)
            ->where('url_hash', $this->normalizer->hash($url))
            ->first();
    }
}
