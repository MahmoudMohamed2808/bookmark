<?php

use App\Models\Bookmark;
use App\Models\User;
use App\Services\BookmarkExportService;


it('exports bookmarks to json with categories and tags', function () {
    $user = User::factory()->create();
    $bookmark = Bookmark::factory()->create([
        'user_id' => $user->id,
        'title' => 'Laravel Docs',
        'url' => 'https://laravel.com/docs',
    ]);
    $bookmark->attachTags(['laravel', 'docs']);

    $json = app(BookmarkExportService::class)->toJson($user);

    $exported = json_decode($json, true);

    expect($exported)->toHaveCount(1)
        ->and($exported[0]['title'])->toBe('Laravel Docs')
        ->and($exported[0]['url'])->toBe('https://laravel.com/docs')
        ->and($exported[0]['tags'])->toBe(['laravel', 'docs']);
});

it('exports bookmarks to csv', function () {
    $user = User::factory()->create();
    Bookmark::factory()->create([
        'user_id' => $user->id,
        'title' => 'GitHub',
        'url' => 'https://github.com',
    ]);

    $csv = app(BookmarkExportService::class)->toCsv($user);

    expect($csv)->toContain('title,url,description,category,tags,notes,created_at')
        ->and($csv)->toContain('GitHub')
        ->and($csv)->toContain('https://github.com');
});

it('exports bookmarks to netscape html format', function () {
    $user = User::factory()->create();
    Bookmark::factory()->create([
        'user_id' => $user->id,
        'title' => 'Laravel',
        'url' => 'https://laravel.com',
        'description' => 'The PHP framework',
        'favicon' => null,
    ]);

    $html = app(BookmarkExportService::class)->toHtml($user);

    expect($html)->toContain('<!DOCTYPE NETSCAPE-Bookmark-file-1>')
        ->and($html)->toContain('<A HREF="https://laravel.com">Laravel</A>')
        ->and($html)->toContain('<DD>The PHP framework');
});

it('does not export other users bookmarks', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    Bookmark::factory()->create(['user_id' => $other->id, 'title' => 'Private']);

    $json = app(BookmarkExportService::class)->toJson($owner);

    expect($json)->not->toContain('Private');
});
