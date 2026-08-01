# Bookmark Manager — Database Schema

Stack notes before the tables:

- **users** — comes from the official React/Inertia/Fortify starter kit. Not listed below; add custom columns via a separate migration if needed later (e.g. `php artisan make:migration add_locale_to_users_table`).
- **tags / taggables** — provided by `spatie/laravel-tags`. Run its own migration (`php artisan vendor:publish --provider="Spatie\Tags\TagsServiceProvider" --tag="migrations"`). No custom model/migration needed.
- **media** — provided by `spatie/laravel-medialibrary`. Run its own migration (`php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"`). No custom model/migration needed.
- **roles / permissions / model_has_roles / etc.** — provided by `spatie/laravel-permission`, used by Filament v5's access control. Run its migration (`php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`). No custom model/migration needed.

Everything below is what you build yourself.

---

## Category

```
php artisan make:model Category -mfs
```

```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
    $table->string('name');
    $table->string('slug');
    $table->string('icon')->nullable();
    $table->string('color')->nullable();
    $table->unsignedInteger('sort_order')->default(0);
    $table->timestamps();

    $table->unique(['user_id', 'slug']);
});
```

---

## Bookmark

```
php artisan make:model Bookmark -mfs
```

```php
Schema::create('bookmarks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
    $table->string('title');
    $table->string('url', 2048);
    $table->string('url_hash', 64)->index(); // sha1 of normalized url, for duplicate detection
    $table->text('description')->nullable();
    $table->string('favicon')->nullable();
    $table->longText('notes')->nullable(); // markdown notes

    $table->timestamp('pinned_at')->nullable();
    $table->timestamp('favorited_at')->nullable();
    $table->timestamp('read_later_at')->nullable();
    $table->timestamp('archived_at')->nullable();

    $table->timestamp('reminder_at')->nullable();
    $table->timestamp('expires_at')->nullable();

    $table->timestamp('last_checked_at')->nullable();
    $table->unsignedSmallInteger('http_status')->nullable();
    $table->boolean('is_broken')->default(false);

    $table->unsignedInteger('visits_count')->default(0);
    $table->timestamp('last_visited_at')->nullable();

    $table->unsignedInteger('sort_order')->default(0);

    $table->timestamps();
    $table->softDeletes(); // trash
});
```

---

## Collection

> Note: alias this model (`use App\Models\Collection as BookmarkCollection;`) where needed to avoid clashing with `Illuminate\Support\Collection`.

```
php artisan make:model Collection -mfs
```

```php
Schema::create('collections', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('slug');
    $table->text('description')->nullable();
    $table->string('icon')->nullable();
    $table->string('color')->nullable();
    $table->unsignedInteger('sort_order')->default(0);
    $table->timestamps();

    $table->unique(['user_id', 'slug']);
});
```

---

## CollectionBookmark (pivot)

```
php artisan make:model CollectionBookmark -ms
```

```php
Schema::create('collection_bookmark', function (Blueprint $table) {
    $table->id();
    $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
    $table->foreignId('bookmark_id')->constrained()->cascadeOnDelete();
    $table->unsignedInteger('sort_order')->default(0);
    $table->timestamps();

    $table->unique(['collection_id', 'bookmark_id']);
});
```

---

## Snippet

*(covers Code Snippets, Favorite Commands, Terminal Notes)*

```
php artisan make:model Snippet -mfs
```

```php
Schema::create('snippets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
    $table->string('title');
    $table->enum('type', ['snippet', 'command', 'terminal_note'])->default('snippet');
    $table->string('language')->nullable(); // php, bash, js, etc.
    $table->longText('content');
    $table->text('description')->nullable();
    $table->timestamps();
    $table->softDeletes();
});
```

---

## BookmarkVisit

*(visit history log, powers Recently Visited / Most Visited analytics)*

```
php artisan make:model BookmarkVisit -ms
```

```php
Schema::create('bookmark_visits', function (Blueprint $table) {
    $table->id();
    $table->foreignId('bookmark_id')->constrained()->cascadeOnDelete();
    $table->timestamp('visited_at');
    $table->timestamps();
});
```

---

## Reminder

*(covers Reminder Bookmarks and Scheduled Review; Expiring Links uses `bookmarks.expires_at` directly)*

```
php artisan make:model Reminder -mfs
```

```php
Schema::create('reminders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('bookmark_id')->constrained()->cascadeOnDelete();
    $table->enum('type', ['reminder', 'scheduled_review'])->default('reminder');
    $table->timestamp('remind_at');
    $table->boolean('is_notified')->default(false);
    $table->timestamp('notified_at')->nullable();
    $table->timestamps();
});
```

---

## ActivityLog

```
php artisan make:model ActivityLog -ms
```

```php
Schema::create('activity_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('bookmark_id')->nullable()->constrained()->nullOnDelete();
    $table->string('action'); // created, updated, deleted, restored, visited, archived, pinned...
    $table->text('description')->nullable();
    $table->json('properties')->nullable();
    $table->timestamps();
});
```

---

## UserSetting

```
php artisan make:model UserSetting -ms
```

```php
Schema::create('user_settings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
    $table->enum('theme', ['light', 'dark', 'system'])->default('system');
    $table->string('theme_color')->nullable();
    $table->enum('language', ['en', 'ar'])->default('en');
    $table->timestamps();
});
```

---

## Backup

*(tracks Backup & Restore / Export jobs)*

```
php artisan make:model Backup -mfs
```

```php
Schema::create('backups', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->enum('type', ['json', 'csv', 'html']);
    $table->string('file_path')->nullable();
    $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
    $table->text('error')->nullable();
    $table->timestamps();
});
```
