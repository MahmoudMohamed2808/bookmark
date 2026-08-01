<?php

namespace App\Services;

use App\Models\Bookmark;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class LinkHealthService
{
    /**
     * Check whether a bookmark's URL is still alive and record the result.
     */
    public function check(Bookmark $bookmark): bool
    {
        try {
            $response = Http::timeout(8)
                ->connectTimeout(3)
                ->head($bookmark->url);
        } catch (ConnectionException) {
            $this->markBroken($bookmark, null);

            return false;
        }

        if ($response->successful() || $response->redirect()) {
            $this->markWorking($bookmark);

            return true;
        }

        $this->markBroken($bookmark, $response->status());

        return false;
    }

    /**
     * Check the health of every bookmark in the given collection.
     */
    public function checkMany(Collection $bookmarks): void
    {
        $bookmarks->each(fn (Bookmark $bookmark) => $this->check($bookmark));
    }

    public function markBroken(Bookmark $bookmark, ?int $httpStatus): void
    {
        $bookmark->forceFill([
            'http_status' => $httpStatus,
            'is_broken' => true,
            'last_checked_at' => now(),
        ])->save();
    }

    public function markWorking(Bookmark $bookmark): void
    {
        $bookmark->forceFill([
            'http_status' => $bookmark->http_status ?: null,
            'is_broken' => false,
            'last_checked_at' => now(),
        ])->save();
    }
}
