<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\Bookmark;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'bookmark_id' => Bookmark::factory(),
            'action' => fake()->randomElement(['created', 'updated', 'deleted', 'visited', 'archived', 'pinned']),
            'description' => fake()->boolean() ? fake()->sentence() : null,
            'properties' => null,
        ];
    }
}
