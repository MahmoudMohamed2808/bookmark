<?php

namespace Database\Factories;

use App\Models\Bookmark;
use App\Models\Category;
use App\Models\User;
use App\Services\UrlNormalizer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bookmark>
 */
class BookmarkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $url = fake()->unique()->url();

        return [
            'user_id' => User::factory(),
            'category_id' => null,
            'title' => fake()->sentence(3),
            'url' => $url,
            'url_hash' => app(UrlNormalizer::class)->hash($url),
            'description' => fake()->boolean() ? fake()->paragraph() : null,
            'favicon' => 'https://www.google.com/s2/favicons?domain='.parse_url($url, PHP_URL_HOST).'&sz=32',
            'notes' => null,
            'pinned_at' => null,
            'favorited_at' => null,
            'read_later_at' => null,
            'archived_at' => null,
            'reminder_at' => null,
            'expires_at' => null,
            'last_checked_at' => null,
            'http_status' => null,
            'is_broken' => false,
            'visits_count' => 0,
            'last_visited_at' => null,
            'sort_order' => 0,
        ];
    }

    public function inCategory(Category $category): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $category->user_id,
            'category_id' => $category->id,
        ]);
    }

    public function pinned(): static
    {
        return $this->state(fn (array $attributes) => ['pinned_at' => now()]);
    }

    public function favorited(): static
    {
        return $this->state(fn (array $attributes) => ['favorited_at' => now()]);
    }

    public function readLater(): static
    {
        return $this->state(fn (array $attributes) => ['read_later_at' => now()]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => ['archived_at' => now()]);
    }

    public function broken(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_broken' => true,
            'http_status' => 404,
            'last_checked_at' => now(),
        ]);
    }

    public function visited(int $count = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'visits_count' => $count,
            'last_visited_at' => now(),
        ]);
    }
}
