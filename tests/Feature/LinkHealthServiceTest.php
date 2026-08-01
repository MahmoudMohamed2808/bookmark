<?php

use App\Models\Bookmark;
use App\Services\LinkHealthService;
use Illuminate\Support\Facades\Http;

it('marks a bookmark working on a successful response', function () {
    $bookmark = Bookmark::factory()->broken()->create(['url' => 'https://example.com']);

    Http::fake(['*example.com*' => Http::response(status: 200)]);

    $service = app(LinkHealthService::class);

    expect($service->check($bookmark))->toBeTrue();

    $bookmark->refresh();

    expect($bookmark->is_broken)->toBeFalse()
        ->and($bookmark->last_checked_at)->not->toBeNull();
});

it('treats redirects as working', function () {
    $bookmark = Bookmark::factory()->create(['url' => 'https://example.com']);

    Http::fake(['*example.com*' => Http::response(status: 301)]);

    $service = app(LinkHealthService::class);

    expect($service->check($bookmark))->toBeTrue();
});

it('marks a bookmark broken on an error response', function () {
    $bookmark = Bookmark::factory()->create(['url' => 'https://example.com/404']);

    Http::fake(['*example.com*' => Http::response(status: 404)]);

    $service = app(LinkHealthService::class);

    expect($service->check($bookmark))->toBeFalse();

    $bookmark->refresh();

    expect($bookmark->is_broken)->toBeTrue()
        ->and($bookmark->http_status)->toBe(404)
        ->and($bookmark->last_checked_at)->not->toBeNull();
});

it('marks a bookmark broken on connection errors', function () {
    $bookmark = Bookmark::factory()->create(['url' => 'https://example.com']);

    Http::fake(fn () => throw new Illuminate\Http\Client\ConnectionException);

    $service = app(LinkHealthService::class);

    expect($service->check($bookmark))->toBeFalse();

    $bookmark->refresh();

    expect($bookmark->is_broken)->toBeTrue()
        ->and($bookmark->http_status)->toBeNull();
});

it('checks many bookmarks', function () {
    $bookmarks = Bookmark::factory()->count(3)->create(['url' => 'https://example.com']);

    Http::fake(['*example.com*' => Http::response(status: 200)]);

    $service = app(LinkHealthService::class);
    $service->checkMany($bookmarks);

    expect(Bookmark::where('is_broken', true)->count())->toBe(0);
});
