<?php

use App\Services\BookmarkMetadataService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->service = new BookmarkMetadataService;
});

it('extracts the title from html', function () {
    $html = '<html><head><title>  My Great   Page </title></head></html>';

    expect($this->service->extractTitle($html))->toBe('My Great Page');
});

it('returns null when no title exists', function () {
    expect($this->service->extractTitle('<html><head></head></html>'))->toBeNull();
});

it('extracts the description meta tag in either attribute order', function () {
    expect($this->service->extractDescription('<meta name="description" content="A cool description">'))
        ->toBe('A cool description');
    expect($this->service->extractDescription('<meta content="Another one" name="description">'))
        ->toBe('Another one');
});

it('returns null when no description exists', function () {
    expect($this->service->extractDescription('<html></html>'))->toBeNull();
});

it('extracts an absolute favicon url', function () {
    $html = '<link rel="icon" href="https://cdn.example.com/favicon.ico">';

    expect($this->service->extractFavicon('https://example.com', $html))
        ->toBe('https://cdn.example.com/favicon.ico');
});

it('resolves relative favicon urls against the page host', function () {
    $html = '<link rel="shortcut icon" href="/assets/favicon.ico">';

    expect($this->service->extractFavicon('https://example.com/deep/page', $html))
        ->toBe('https://example.com/assets/favicon.ico');
});

it('returns null when no favicon exists', function () {
    expect($this->service->extractFavicon('https://example.com', '<html></html>'))->toBeNull();
});

it('fetches metadata for a url', function () {
    $html = <<<'HTML'
        <html>
        <head>
            <title>Example Domain</title>
            <meta name="description" content="An example page">
            <link rel="icon" href="/favicon.ico">
        </head>
        </html>
        HTML;

    Http::fake(['*example.com*' => Http::response($html)]);

    $metadata = $this->service->fetch('https://example.com');

    expect($metadata)->toBe([
        'title' => 'Example Domain',
        'favicon' => 'https://example.com/favicon.ico',
        'description' => 'An example page',
    ]);
});

it('returns nulls when the request fails', function () {
    Http::fake(['*example.com*' => Http::response(status: 500)]);

    expect($this->service->fetch('https://example.com'))
        ->toBe(['title' => null, 'favicon' => null, 'description' => null]);
});

it('returns nulls on connection errors', function () {
    Http::fake(fn () => throw new Illuminate\Http\Client\ConnectionException);

    expect($this->service->fetch('https://example.com'))
        ->toBe(['title' => null, 'favicon' => null, 'description' => null]);
});

it('extracts metadata from the facade', function () {
    Http::fake(['*example.com*' => Http::response('<html><head><title>Facade Title</title></head></html>')]);

    $metadata = App\Facades\BookmarkMetadata::fetch('https://example.com');

    expect($metadata['title'])->toBe('Facade Title');
});
