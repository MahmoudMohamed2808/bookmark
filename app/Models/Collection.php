<?php

namespace App\Models;

use Database\Factories\CollectionFactory;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $icon
 * @property string|null $color
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
#[Fillable(['user_id', 'name', 'slug', 'description', 'icon', 'color', 'sort_order'])]
class Collection extends Model
{
    /** @use HasFactory<CollectionFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookmarks(): BelongsToMany
    {
        return $this->belongsToMany(Bookmark::class, 'collection_bookmark')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    /**
     * Return the icon only if it is a valid Heroicon name.
     */
    public function iconSvgName(): ?string
    {
        $icon = $this->icon;

        if ($icon === null || $icon === '') {
            return null;
        }

        $name = preg_replace('/^heroicon-o-/', 'o-', $icon);

        return Heroicon::tryFrom($name) !== null ? $icon : null;
    }
}
