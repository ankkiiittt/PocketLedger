<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Budget>
 */
class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => fn (array $attributes) => Category::factory()->expense()->create([
                'user_id' => $attributes['user_id'],
            ])->id,
            'month' => now()->startOfMonth()->format('Y-m-d'),
            'amount_limit' => sprintf('%.2f', fake()->randomFloat(2, 500, 20000)),
        ];
    }
}
