<?php

namespace App\Models;

use Database\Factories\BookmarkFactory;
use App\Services\UrlNormalizer;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Tags\HasTags;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $category_id
 * @property string $title
 * @property string $url
 * @property string $url_hash
 * @property string|null $description
 * @property string|null $favicon
 * @property string|null $notes
 * @property Carbon|null $pinned_at
 * @property Carbon|null $favorited_at
 * @property Carbon|null $read_later_at
 * @property Carbon|null $archived_at
 * @property Carbon|null $reminder_at
 * @property Carbon|null $expires_at
 * @property Carbon|null $last_checked_at
 * @property int|null $http_status
 * @property bool $is_broken
 * @property int $visits_count
 * @property Carbon|null $last_visited_at
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'user_id', 'category_id', 'title', 'url', 'url_hash', 'description', 'favicon', 'notes',
    'pinned_at', 'favorited_at', 'read_later_at', 'archived_at',
    'reminder_at', 'expires_at', 'last_checked_at', 'http_status', 'is_broken',
    'visits_count', 'last_visited_at', 'sort_order',
])]
#[Hidden(['url_hash'])]
class Bookmark extends Model implements HasMedia
{
    /** @use HasFactory<BookmarkFactory> */
    use HasFactory, HasTags, InteractsWithMedia, SoftDeletes;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_bookmark')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(BookmarkVisit::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function isPinned(): bool
    {
        return $this->pinned_at !== null;
    }

    public function isFavorited(): bool
    {
        return $this->favorited_at !== null;
    }

    public function isInReadLater(): bool
    {
        return $this->read_later_at !== null;
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    protected static function booted(): void
    {
        static::creating(function (Bookmark $bookmark): void {
            if (! $bookmark->url_hash && $bookmark->url) {
                $bookmark->url_hash = app(UrlNormalizer::class)->hash($bookmark->url);
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pinned_at' => 'datetime',
            'favorited_at' => 'datetime',
            'read_later_at' => 'datetime',
            'archived_at' => 'datetime',
            'reminder_at' => 'datetime',
            'expires_at' => 'datetime',
            'last_checked_at' => 'datetime',
            'http_status' => 'integer',
            'is_broken' => 'boolean',
            'visits_count' => 'integer',
            'last_visited_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }
}
