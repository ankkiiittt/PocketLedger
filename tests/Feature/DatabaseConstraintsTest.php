<?php

declare(strict_types=1);

use App\Enums\CategoryType;
use App\Enums\Frequency;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('database rejects duplicate category name for the same user and type', function () {
    $user = User::factory()->create();

    $user->categories()->create([
        'name' => 'Groceries',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    expect(function () use ($user) {
        $user->categories()->create([
            'name' => 'groceries', // case-insensitive duplicate
            'type' => CategoryType::Expense,
            'color' => '#B4432F',
        ]);
    })->toThrow(QueryException::class);
});

test('database allows same category name for different types or different users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // Same user, different type (Income vs Expense)
    $cat1 = $user1->categories()->create([
        'name' => 'Other',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    $cat2 = $user1->categories()->create([
        'name' => 'Other',
        'type' => CategoryType::Income,
        'color' => '#2E7D4F',
    ]);

    // Different user, same name & type
    $cat3 = $user2->categories()->create([
        'name' => 'Other',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    expect($cat1->exists)->toBeTrue()
        ->and($cat2->exists)->toBeTrue()
        ->and($cat3->exists)->toBeTrue();
});

test('database rejects negative or zero amount on transactions via CHECK constraint', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);

    expect(function () use ($user, $category) {
        $user->transactions()->create([
            'category_id' => $category->id,
            'amount' => '-10.50',
            'transaction_date' => '2026-10-18',
        ]);
    })->toThrow(QueryException::class);

    expect(function () use ($user, $category) {
        $user->transactions()->create([
            'category_id' => $category->id,
            'amount' => '0.00',
            'transaction_date' => '2026-10-18',
        ]);
    })->toThrow(QueryException::class);
});

test('database rejects duplicate recurring transaction occurrence for the same rule and date', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);

    $rule = $user->recurringTransactions()->create([
        'category_id' => $category->id,
        'amount' => '500.00',
        'frequency' => Frequency::Monthly,
        'start_date' => '2026-10-01',
        'next_run_date' => '2026-10-01',
    ]);

    $user->transactions()->create([
        'category_id' => $category->id,
        'amount' => '500.00',
        'transaction_date' => '2026-10-01',
        'recurring_transaction_id' => $rule->id,
    ]);

    expect(function () use ($user, $category, $rule) {
        $user->transactions()->create([
            'category_id' => $category->id,
            'amount' => '500.00',
            'transaction_date' => '2026-10-01', // same date and same rule
            'recurring_transaction_id' => $rule->id,
        ]);
    })->toThrow(QueryException::class);
});

test('database prevents deleting a category that has transactions due to RESTRICT', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);

    $user->transactions()->create([
        'category_id' => $category->id,
        'amount' => '100.00',
        'transaction_date' => '2026-10-18',
    ]);

    expect(function () use ($category) {
        $category->delete();
    })->toThrow(QueryException::class);
});
