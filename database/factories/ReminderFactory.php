<?php

namespace Database\Factories;

use App\Models\Bookmark;
use App\Models\Reminder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reminder>
 */
class ReminderFactory extends Factory
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
            'type' => 'reminder',
            'remind_at' => fake()->dateTimeBetween('now', '+30 days'),
            'is_notified' => false,
            'notified_at' => null,
        ];
    }

    public function due(): static
    {
        return $this->state(fn (array $attributes) => [
            'remind_at' => now()->subHour(),
            'is_notified' => false,
        ]);
    }

    public function notified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_notified' => true,
            'notified_at' => now(),
        ]);
    }
}
