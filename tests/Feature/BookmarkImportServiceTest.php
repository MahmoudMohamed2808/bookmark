<?php

use App\Models\Bookmark;
use App\Models\Category;
use App\Models\User;
use App\Services\BookmarkImportService;
use Illuminate\Http\UploadedFile;

it('imports bookmarks and creates nested categories from folders', function () {
    $user = User::factory()->create();

    $file = UploadedFile::fake()->createWithContent('bookmarks.html', <<<'HTML'
        <!DOCTYPE NETSCAPE-Bookmark-file-1>
        <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
        <TITLE>Bookmarks</TITLE>
        <H1>Bookmarks</H1>
        <DL><p>
            <DT><H3>Laravel</H3>
            <DL><p>
                <DT><A HREF="https://laravel.com/docs" ADD_DATE="1">Laravel Docs</A>
                <DT><H3>Packages</H3>
                <DL><p>
                    <DT><A HREF="https://spatie.be/docs" ICON="https://spatie.be/favicon.ico">Spatie Docs</A>
                </DL><p>
            </DL><p>
            <DT><A HREF="https://github.com" ADD_DATE="2">GitHub</A>
        </DL><p>
        HTML);

    $count = app(BookmarkImportService::class)->importFromHtml($file, $user);

    expect($count)->toBe(3);

    $laravel = Category::where('slug', 'like', 'laravel-%')->where('user_id', $user->id)->firstOrFail();
    $packages = Category::where('slug', 'like', 'packages-%')->where('user_id', $user->id)->firstOrFail();

    expect($laravel->parent_id)->toBeNull()
        ->and($packages->parent_id)->toBe($laravel->id);

    $docs = Bookmark::where('url', 'https://laravel.com/docs')->where('user_id', $user->id)->firstOrFail();
    $spatie = Bookmark::where('url', 'https://spatie.be/docs')->where('user_id', $user->id)->firstOrFail();
    $github = Bookmark::where('url', 'https://github.com')->where('user_id', $user->id)->firstOrFail();

    expect($docs->category_id)->toBe($laravel->id)
        ->and($spatie->category_id)->toBe($packages->id)
        ->and($github->category_id)->toBeNull()
        ->and($spatie->favicon)->toBe('https://spatie.be/favicon.ico');
});

it('computes the url hash for imported bookmarks', function () {
    $user = User::factory()->create();

    $file = UploadedFile::fake()->createWithContent('bookmarks.html', <<<'HTML'
        <DL><p>
            <DT><A HREF="https://example.com/page">Example</A>
        </DL><p>
        HTML);

    app(BookmarkImportService::class)->importFromHtml($file, $user);

    $bookmark = Bookmark::where('user_id', $user->id)->firstOrFail();

    expect($bookmark->url_hash)->toBe(sha1('https://example.com/page'));
});

it('skips non-http links', function () {
    $user = User::factory()->create();

    $file = UploadedFile::fake()->createWithContent('bookmarks.html', <<<'HTML'
        <DL><p>
            <DT><A HREF="javascript:void(0)">Skip me</A>
        </DL><p>
        HTML);

    $count = app(BookmarkImportService::class)->importFromHtml($file, $user);

    expect($count)->toBe(0)
        ->and(Bookmark::where('user_id', $user->id)->count())->toBe(0);
});
