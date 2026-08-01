<?php

use App\Services\UrlNormalizer;

it('forces a scheme when missing', function () {
    $normalizer = new UrlNormalizer;

    expect($normalizer->normalize('example.com/path'))->toBe('https://example.com/path');
});

it('lowercases the host', function () {
    $normalizer = new UrlNormalizer;

    expect($normalizer->normalize('https://EXAMPLE.com/Path'))->toBe('https://example.com/Path');
});

it('strips utm tracking parameters', function () {
    $normalizer = new UrlNormalizer;

    expect($normalizer->normalize('https://example.com/page?utm_source=newsletter&utm_medium=email&id=42'))
        ->toBe('https://example.com/page?id=42');
});

it('removes the trailing slash from the path', function () {
    $normalizer = new UrlNormalizer;

    expect($normalizer->normalize('https://example.com/path/'))->toBe('https://example.com/path');
});

it('keeps the root path slash', function () {
    $normalizer = new UrlNormalizer;

    expect($normalizer->normalize('https://example.com/'))->toBe('https://example.com/');
});

it('removes default ports', function () {
    $normalizer = new UrlNormalizer;

    expect($normalizer->normalize('https://example.com:443/path'))->toBe('https://example.com/path');
    expect($normalizer->normalize('http://example.com:80/path'))->toBe('http://example.com/path');
});

it('keeps non-default ports', function () {
    $normalizer = new UrlNormalizer;

    expect($normalizer->normalize('https://example.com:8443/path'))->toBe('https://example.com:8443/path');
});

it('hashes to a sha1 of the normalized url', function () {
    $normalizer = new UrlNormalizer;

    expect($normalizer->hash('HTTPS://Example.com/path?utm_source=x'))
        ->toBe(sha1('https://example.com/path'));
});

it('returns an empty string for blank input', function () {
    $normalizer = new UrlNormalizer;

    expect($normalizer->normalize('  '))->toBe('');
});
