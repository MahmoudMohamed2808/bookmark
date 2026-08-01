# Models, Services, Service Provider & Facades

---

## Models
`App\Models\*` — relationships only, no field-level detail (already covered in the schema file).

### User
*(from starter kit — extend with these relationships)*
- `hasMany` Bookmark
- `hasMany` Category
- `hasMany` Collection
- `hasMany` Snippet
- `hasMany` Backup
- `hasMany` ActivityLog
- `hasOne` UserSetting

### Category
- `belongsTo` User
- `belongsTo` Category (`parent`)
- `hasMany` Category (`children`)
- `hasMany` Bookmark
- `hasMany` Snippet

### Bookmark
- `belongsTo` User
- `belongsTo` Category
- `belongsToMany` Collection (pivot: `collection_bookmark`, with `sort_order`)
- `hasMany` Reminder
- `hasMany` BookmarkVisit
- `hasMany` ActivityLog
- `morphToMany` Tag (via `spatie/laravel-tags` `HasTags` trait)
- `InteractsWithMedia` (via `spatie/laravel-medialibrary` trait, for attached images/files)
- `SoftDeletes`

### Collection
- `belongsTo` User
- `belongsToMany` Bookmark (pivot: `collection_bookmark`, with `sort_order`)

### Snippet
- `belongsTo` User
- `belongsTo` Category
- `SoftDeletes`

### BookmarkVisit
- `belongsTo` Bookmark

### Reminder
- `belongsTo` Bookmark

### ActivityLog
- `belongsTo` User
- `belongsTo` Bookmark (nullable)

### UserSetting
- `belongsTo` User

### Backup
- `belongsTo` User

---

## Services
`App\Services\*` — responsibility + public method signatures only, no implementation.

### UrlNormalizer
Normalizes URLs so duplicate detection and validation compare like-for-like (strips `utm_*` params, trailing slash, forces scheme, lowercases host).
```php
normalize(string $url): string
hash(string $url): string
```

### UrlValidationService
Validates a URL is well-formed and reachable before saving.
```php
isValid(string $url): bool
isReachable(string $url): bool
```

### DuplicateDetectionService
Uses `UrlNormalizer` to find existing bookmarks with the same normalized URL for a user.
```php
findDuplicate(string $url, int $userId): ?Bookmark
```

### BookmarkMetadataService
Fetches title, favicon, and description for a given URL (Auto Fetch features).
```php
fetch(string $url): array
extractTitle(string $html): ?string
extractFavicon(string $url, string $html): ?string
extractDescription(string $html): ?string
```

### LinkHealthService
Checks whether a bookmark's URL is still alive (Detect Broken Links / Health Check).
```php
check(Bookmark $bookmark): bool
checkMany(Collection $bookmarks): void
markBroken(Bookmark $bookmark, int $httpStatus): void
markWorking(Bookmark $bookmark): void
```

### BookmarkImportService
Parses a Netscape-format HTML bookmarks file and creates records.
```php
importFromHtml(UploadedFile $file, User $user): int
```

### BookmarkExportService
Builds export files in the supported formats.
```php
toJson(User $user): string
toCsv(User $user): string
toHtml(User $user): string
```

### BackupService
Orchestrates full backup generation and restore.
```php
generate(User $user, string $type): Backup
restore(Backup $backup): void
```

### ReminderService
Schedules, snoozes, and dispatches due reminder/scheduled-review notifications.
```php
schedule(Bookmark $bookmark, Carbon $remindAt, string $type = 'reminder'): Reminder
snooze(Reminder $reminder, Carbon $newTime): void
sendDueNotifications(): void
```

### ActivityLogService
Central place every other service/model observer calls to record activity.
```php
log(User $user, string $action, ?Bookmark $bookmark = null, array $properties = []): ActivityLog
```

---

## Service Provider

```
php artisan make:provider BookmarkManagerServiceProvider
```

Register services as singletons and bind the facade accessor inside `register()`:

```php
public function register(): void
{
    $this->app->singleton(\App\Services\UrlNormalizer::class);
    $this->app->singleton(\App\Services\UrlValidationService::class);
    $this->app->singleton(\App\Services\DuplicateDetectionService::class);
    $this->app->singleton(\App\Services\BookmarkMetadataService::class);
    $this->app->singleton(\App\Services\LinkHealthService::class);
    $this->app->singleton(\App\Services\BookmarkImportService::class);
    $this->app->singleton(\App\Services\BookmarkExportService::class);
    $this->app->singleton(\App\Services\BackupService::class);
    $this->app->singleton(\App\Services\ReminderService::class);
    $this->app->singleton(\App\Services\ActivityLogService::class);

    $this->app->singleton('bookmark-metadata', fn ($app) => $app->make(\App\Services\BookmarkMetadataService::class));
}
```

Then register the provider in `bootstrap/providers.php` (Laravel 13):

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\BookmarkManagerServiceProvider::class,
];
```

---

## Facade

Create `app/Facades/BookmarkMetadata.php` manually (no artisan command for facades):

```php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class BookmarkMetadata extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'bookmark-metadata';
    }
}
```

Usage anywhere in the app:

```php
use App\Facades\BookmarkMetadata;

$data = BookmarkMetadata::fetch($url);
```

> The same pattern (container binding key + thin `Facade` subclass) can be repeated for any other service you want accessible statically — e.g. `LinkHealth::check($bookmark)` — but most of the services above are better injected via constructor/dependency injection than turned into facades. Reserve facades for the one or two you call from many unrelated places (metadata fetching is a good candidate; import/export/backup are not).
