<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array fetch(string $url)
 * @method static string|null extractTitle(string $html)
 * @method static string|null extractFavicon(string $url, string $html)
 * @method static string|null extractDescription(string $html)
 *
 * @see \App\Services\BookmarkMetadataService
 */
class BookmarkMetadata extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'bookmark-metadata';
    }
}
