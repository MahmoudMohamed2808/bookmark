<?php

namespace App\Models;

use Database\Factories\CollectionBookmarkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $collection_id
 * @property int $bookmark_id
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
#[Fillable(['collection_id', 'bookmark_id', 'sort_order'])]
class CollectionBookmark extends Model
{
    /** @use HasFactory<CollectionBookmarkFactory> */
    use HasFactory;

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function bookmark(): BelongsTo
    {
        return $this->belongsTo(Bookmark::class);
    }
}
