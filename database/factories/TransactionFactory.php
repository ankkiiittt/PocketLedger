<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

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
            'amount' => sprintf('%.2f', fake()->randomFloat(2, 5, 2500)),
            'transaction_date' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'description' => fake()->sentence(3),
            'recurring_transaction_id' => null,
        ];
    }
}
