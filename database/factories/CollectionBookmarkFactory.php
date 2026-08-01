<?php

namespace Database\Factories;

use App\Models\Bookmark;
use App\Models\Collection;
use App\Models\CollectionBookmark;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CollectionBookmark>
 */
class CollectionBookmarkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'collection_id' => Collection::factory(),
            'bookmark_id' => Bookmark::factory(),
            'sort_order' => 0,
        ];
    }
}
