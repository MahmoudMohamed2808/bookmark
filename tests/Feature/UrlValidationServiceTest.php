<?php

use App\Services\UrlNormalizer;
use App\Services\UrlValidationService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->service = new UrlValidationService(new UrlNormalizer);
});

it('validates well-formed urls', function (string $url) {
    expect($this->service->isValid($url))->toBeTrue();
})->with([
    'https' => 'https://example.com',
    'with path' => 'https://example.com/path?q=1#frag',
    'no scheme' => 'example.com',
]);

it('rejects malformed urls', function (string $url) {
    expect($this->service->isValid($url))->toBeFalse();
})->with([
    'not a url' => 'not a url',
    'empty' => '',
]);

it('reports reachable urls', function () {
    Http::fake(['*example.com*' => Http::response(status: 200)]);

    expect($this->service->isReachable('https://example.com'))->toBeTrue();
});

it('reports redirects as reachable', function () {
    Http::fake([
        '*example.com*' => Http::response(status: 301, headers: ['Location' => 'https://example.org']),
        '*example.org*' => Http::response(status: 200),
    ]);

    expect($this->service->isReachable('https://example.com'))->toBeTrue();
});

it('reports failing urls as unreachable', function () {
    Http::fake(['*example.com*' => Http::response(status: 404)]);

    expect($this->service->isReachable('https://example.com/missing'))->toBeFalse();
});

it('reports connection errors as unreachable', function () {
    Http::fake(fn () => throw new Illuminate\Http\Client\ConnectionException);

    expect($this->service->isReachable('https://example.com'))->toBeFalse();
});

it('does not attempt requests for invalid urls', function () {
    Http::fake();

    expect($this->service->isReachable('not a url'))->toBeFalse();
    Http::assertNothingSent();
});
