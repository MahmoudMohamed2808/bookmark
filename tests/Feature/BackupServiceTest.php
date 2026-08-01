<?php

use App\Models\Backup;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

it('generates a json backup and stores the file', function () {
    $user = User::factory()->create();
    Bookmark::factory()->count(2)->create(['user_id' => $user->id]);

    $backup = app(BackupService::class)->generate($user, 'json');

    expect($backup->status)->toBe('completed')
        ->and($backup->file_path)->not->toBeNull();

    Storage::disk('local')->assertExists($backup->file_path);
});

it('rejects unsupported backup types', function () {
    $user = User::factory()->create();

    app(BackupService::class)->generate($user, 'invalid');
})->throws(InvalidArgumentException::class);

it('restores bookmarks and categories from a json backup', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Laravel']);

    $bookmark = Bookmark::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Laravel Docs',
        'url' => 'https://laravel.com/docs',
    ]);
    $bookmark->attachTags(['laravel']);

    $backup = app(BackupService::class)->generate($user, 'json');
    expect($backup->status)->toBe('completed');

    Bookmark::factory()->create(['user_id' => $user->id, 'title' => 'To be removed']);

    app(BackupService::class)->restore($backup);

    expect(Bookmark::where('user_id', $user->id)->count())->toBe(1)
        ->and(Bookmark::where('user_id', $user->id)->first()->title)->toBe('Laravel Docs');

    $restored = Bookmark::where('user_id', $user->id)->firstOrFail();

    expect($restored->category)->not->toBeNull()
        ->and($restored->category->name)->toBe('Laravel')
        ->and($restored->tags->pluck('name'))->toContain('laravel');
});

it('refuses to restore non-json or incomplete backups', function () {
    $user = User::factory()->create();
    $backup = Backup::factory()->create([
        'user_id' => $user->id,
        'type' => 'csv',
        'status' => 'completed',
    ]);

    app(BackupService::class)->restore($backup);
})->throws(InvalidArgumentException::class);
