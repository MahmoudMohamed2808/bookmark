<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'user_id' => User::factory(),
            'parent_id' => null,
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'icon' => fake()->boolean() ? 'heroicon-o-folder' : null,
            'color' => fake()->safeHexColor(),
            'sort_order' => 0,
        ];
    }

    public function childOf(Category $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $parent->user_id,
            'parent_id' => $parent->id,
        ]);
    }
}
