<?php

namespace Database\Factories;

use App\Models\Bookmark;
use App\Models\BookmarkVisit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookmarkVisit>
 */
class BookmarkVisitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bookmark_id' => Bookmark::factory(),
            'visited_at' => fake()->dateTimeBetween('-30 days'),
        ];
    }
}
