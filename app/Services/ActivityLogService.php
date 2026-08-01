<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Bookmark;
use App\Models\User;

class ActivityLogService
{
    /**
     * Record a single activity entry.
     *
     * @param  array<string, mixed>  $properties
     */
    public function log(User $user, string $action, ?Bookmark $bookmark = null, array $properties = []): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $user->id,
            'bookmark_id' => $bookmark?->id,
            'action' => $action,
            'properties' => $properties === [] ? null : $properties,
        ]);
    }
}
