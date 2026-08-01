<?php

namespace App\Providers;

use App\Services\ActivityLogService;
use App\Services\BackupService;
use App\Services\BookmarkExportService;
use App\Services\BookmarkImportService;
use App\Services\BookmarkMetadataService;
use App\Services\DuplicateDetectionService;
use App\Services\LinkHealthService;
use App\Services\ReminderService;
use App\Services\UrlNormalizer;
use App\Services\UrlValidationService;
use Illuminate\Support\ServiceProvider;

class BookmarkManagerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(UrlNormalizer::class);
        $this->app->singleton(UrlValidationService::class);
        $this->app->singleton(DuplicateDetectionService::class);
        $this->app->singleton(BookmarkMetadataService::class);
        $this->app->singleton(LinkHealthService::class);
        $this->app->singleton(BookmarkImportService::class);
        $this->app->singleton(BookmarkExportService::class);
        $this->app->singleton(BackupService::class);
        $this->app->singleton(ReminderService::class);
        $this->app->singleton(ActivityLogService::class);

        $this->app->singleton('bookmark-metadata', fn ($app) => $app->make(BookmarkMetadataService::class));
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
