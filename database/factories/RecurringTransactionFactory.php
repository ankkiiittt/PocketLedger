<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Frequency;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecurringTransaction>
 */
class RecurringTransactionFactory extends Factory
{
    protected $model = RecurringTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => fn (array $attributes) => Category::factory()->create([
                'user_id' => $attributes['user_id'],
            ])->id,
            'amount' => sprintf('%.2f', fake()->randomFloat(2, 50, 10000)),
            'description' => fake()->sentence(3),
            'frequency' => Frequency::Monthly,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => null,
            'next_run_date' => now()->format('Y-m-d'),
            'last_run_date' => null,
            'runs_count' => 0,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the rule is paused.
     */
    public function paused(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the rule has ended.
     */
    public function ended(): static
    {
        return $this->state(fn () => [
            'next_run_date' => null,
        ]);
    }
}
