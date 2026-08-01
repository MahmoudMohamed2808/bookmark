<?php

use App\Models\Bookmark;
use App\Models\User;
use App\Services\ActivityLogService;

it('logs an activity entry for a user', function () {
    $user = User::factory()->create();

    $log = app(ActivityLogService::class)->log($user, 'created');

    expect($log->user_id)->toBe($user->id)
        ->and($log->action)->toBe('created')
        ->and($log->bookmark_id)->toBeNull()
        ->and($log->properties)->toBeNull();
});

it('attaches a bookmark and properties to the log', function () {
    $user = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['user_id' => $user->id]);

    $log = app(ActivityLogService::class)->log($user, 'archived', $bookmark, ['archived_at' => '2026-07-31']);

    expect($log->bookmark_id)->toBe($bookmark->id)
        ->and($log->properties)->toBe(['archived_at' => '2026-07-31']);
});
