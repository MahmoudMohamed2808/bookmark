<?php

use App\Models\Bookmark;
use App\Models\Category;
use App\Models\User;
use App\Services\UrlNormalizer;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

test('guests are redirected to the login page', function (): void {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('the dashboard shows stats and bookmark lists', function (): void {
    $category = Category::factory()->for($this->user)->create();

    Bookmark::factory()->for($this->user)->count(3)->create(['category_id' => $category->id]);
    Bookmark::factory()->for($this->user)->pinned()->favorited()->create();
    Bookmark::factory()->for($this->user)->readLater()->create();
    Bookmark::factory()->for($this->user)->visited(12)->create();

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('stats.total', 6)
            ->where('stats.pinned', 1)
            ->where('stats.favorites', 1)
            ->where('stats.readLater', 1)
            ->has('bookmarks', 6)
            ->has('pinned', 1)
            ->has('readLater', 1)
            ->has('mostVisited', 5)
            ->where('mostVisited.0.visits_count', 12)
            ->where('categories.0.name', $category->name));
});

test('archived bookmarks are hidden from the recent list', function (): void {
    Bookmark::factory()->for($this->user)->archived()->create();

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page->has('bookmarks', 0));
});

test('the dashboard filters bookmarks by search query', function (): void {
    Bookmark::factory()->for($this->user)->create(['title' => 'Laravel Documentation']);
    Bookmark::factory()->for($this->user)->create(['title' => 'React documentation']);
    Bookmark::factory()->for($this->user)->create(['title' => 'Laravel News']);

    $this->actingAs($this->user)
        ->get(route('dashboard', ['q' => 'react']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('q', 'react')
            ->has('bookmarks', 1)
            ->where('bookmarks.0.title', 'React documentation'));
});

test('a bookmark can be added from the dashboard', function (): void {
    $this->actingAs($this->user)
        ->post(route('dashboard.bookmarks.store'), [
            'url' => 'https://laravel.com/docs',
            'title' => 'Laravel Docs',
        ])
        ->assertRedirect();

    $bookmark = Bookmark::where('url_hash', app(UrlNormalizer::class)->hash('https://laravel.com/docs'))->first();

    expect($bookmark)->not->toBeNull()
        ->and($bookmark->user_id)->toBe($this->user->id)
        ->and($bookmark->title)->toBe('Laravel Docs')
        ->and($bookmark->url_hash)->not->toBeNull()
        ->and($this->user->activityLogs()->where('action', 'created')->count())->toBe(1);
});

test('a bookmark without a title gets its metadata fetched', function (): void {
    Http::fake([
        'laravel.com*' => Http::response('<html><head><title>Laravel</title></head></html>', 200),
    ]);

    $this->actingAs($this->user)
        ->post(route('dashboard.bookmarks.store'), ['url' => 'https://laravel.com'])
        ->assertRedirect();

    expect(Bookmark::first()->title)->toBe('Laravel');
});

test('duplicate URLs are not saved twice', function (): void {
    $url = 'https://example.com/article';
    $normalizer = app(UrlNormalizer::class);

    Bookmark::factory()->for($this->user)->create([
        'url' => $url,
        'url_hash' => $normalizer->hash($url),
    ]);

    $this->actingAs($this->user)
        ->post(route('dashboard.bookmarks.store'), ['url' => $url])
        ->assertRedirect()
        ->assertSessionHas('inertia');

    expect(Bookmark::count())->toBe(1);
});

test('an invalid URL is rejected', function (): void {
    $this->actingAs($this->user)
        ->post(route('dashboard.bookmarks.store'), ['url' => 'not-a-url'])
        ->assertSessionHasErrors('url');

    expect(Bookmark::count())->toBe(0);
});

test('a bookmark can be added to an existing category', function (): void {
    $category = Category::factory()->for($this->user)->create();

    $this->actingAs($this->user)
        ->post(route('dashboard.bookmarks.store'), [
            'url' => 'https://example.com/article',
            'title' => 'Example',
            'category_id' => $category->id,
        ])
        ->assertRedirect();

    expect(Bookmark::first()->category_id)->toBe($category->id);
});
