<?php

use App\Models\Bookmark;
use App\Models\User;
use App\Services\DuplicateDetectionService;
use App\Services\UrlNormalizer;

it('finds a duplicate by normalized url', function () {
    $user = User::factory()->create();
    $existing = Bookmark::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.com/page',
        'url_hash' => (new UrlNormalizer)->hash('https://example.com/page'),
    ]);

    $service = app(DuplicateDetectionService::class);

    $duplicate = $service->findDuplicate('https://example.com/page?utm_source=newsletter', $user->id);

    expect($duplicate)->not->toBeNull();
    expect($duplicate->id)->toBe($existing->id);
});

it('ignores bookmarks owned by other users', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    Bookmark::factory()->create([
        'user_id' => $owner->id,
        'url' => 'https://example.com/page',
    ]);

    $service = app(DuplicateDetectionService::class);

    expect($service->findDuplicate('https://example.com/page', $other->id))->toBeNull();
});

it('returns null when no duplicate exists', function () {
    $user = User::factory()->create();

    $service = app(DuplicateDetectionService::class);

    expect($service->findDuplicate('https://example.com/unique', $user->id))->toBeNull();
});
