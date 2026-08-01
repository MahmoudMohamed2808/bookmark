<?php

namespace App\Models;

use Database\Factories\BookmarkVisitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $bookmark_id
 * @property Carbon $visited_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['bookmark_id', 'visited_at'])]
class BookmarkVisit extends Model
{
    /** @use HasFactory<BookmarkVisitFactory> */
    use HasFactory;

    public function bookmark(): BelongsTo
    {
        return $this->belongsTo(Bookmark::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
        ];
    }
}
