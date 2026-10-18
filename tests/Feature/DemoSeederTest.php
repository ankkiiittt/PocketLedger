<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('demo seeder creates demo user with realistic data', function () {
    $this->seed(DemoSeeder::class);

    $user = User::where('email', 'demo@pocketledger.test')->first();
    expect($user)->not->toBeNull()
        ->and($user?->currency_code)->toBe('INR')
        ->and($user?->name)->toBe('Demo User');

    // Password matches 'password'
    expect(auth()->attempt(['email' => 'demo@pocketledger.test', 'password' => 'password']))->toBeTrue();

    // Default categories exist
    expect($user?->categories()->count())->toBe(14);

    // Realistic transactions generated
    expect($user?->transactions()->count())->toBeGreaterThan(100);

    // Budgets exist for expense categories
    expect($user?->budgets()->count())->toBeGreaterThanOrEqual(16);

    // Recurring rules created
    expect($user?->recurringTransactions()->count())->toBe(2);
});
