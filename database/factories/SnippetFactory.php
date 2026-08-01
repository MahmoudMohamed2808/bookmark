<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Snippet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Snippet>
 */
class SnippetFactory extends Factory
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
            'category_id' => null,
            'title' => fake()->sentence(3),
            'type' => fake()->randomElement(['snippet', 'command', 'terminal_note']),
            'language' => 'php',
            'content' => fake()->paragraphs(2, true),
            'description' => fake()->boolean() ? fake()->sentence() : null,
        ];
    }

    public function inCategory(Category $category): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $category->user_id,
            'category_id' => $category->id,
        ]);
    }

    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => ['type' => $type]);
    }
}
