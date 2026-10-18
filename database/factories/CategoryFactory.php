<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->word(),
            'type' => fake()->randomElement([CategoryType::Income, CategoryType::Expense]),
            'color' => fake()->randomElement(['#1F5F4D', '#B4432F', '#2F5F8A', '#B7791F', '#6B4E8F', '#3D8B8B', '#8A5A44', '#6F7D3A']),
            'is_archived' => false,
        ];
    }

    /**
     * Indicate that the category is an expense category.
     */
    public function expense(): static
    {
        return $this->state(fn () => [
            'type' => CategoryType::Expense,
        ]);
    }

    /**
     * Indicate that the category is an income category.
     */
    public function income(): static
    {
        return $this->state(fn () => [
            'type' => CategoryType::Income,
        ]);
    }

    /**
     * Indicate that the category is archived.
     */
    public function archived(): static
    {
        return $this->state(fn () => [
            'is_archived' => true,
        ]);
    }
}
