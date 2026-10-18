<?php

declare(strict_types=1);

use App\Enums\CategoryType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to login from dashboard', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('user sees empty states when having no transactions or budgets in month', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertSee('Nothing recorded for this month yet');
    $response->assertSee('No budgets set for this month');
});

test('dashboard displays summary cards, top budgets, and recent transactions', function () {
    $user = User::factory()->create(['currency_code' => 'INR']);

    $expenseCat1 = $user->categories()->create([
        'name' => 'Groceries',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);
    $expenseCat2 = $user->categories()->create([
        'name' => 'Rent',
        'type' => CategoryType::Expense,
        'color' => '#B4432F',
    ]);
    $incomeCat = $user->categories()->create([
        'name' => 'Salary',
        'type' => CategoryType::Income,
        'color' => '#2E7D4F',
    ]);

    $monthDate = Carbon::now()->startOfMonth()->toDateString();
    $today = Carbon::today()->toDateString();

    // Set budgets
    $user->budgets()->create(['category_id' => $expenseCat1->id, 'month' => $monthDate, 'amount_limit' => '1000.00']);
    $user->budgets()->create(['category_id' => $expenseCat2->id, 'month' => $monthDate, 'amount_limit' => '2000.00']);

    // Create transactions:
    // Income: 5000.00
    $user->transactions()->create([
        'category_id' => $incomeCat->id,
        'amount' => '5000.00',
        'transaction_date' => $today,
        'description' => 'Paycheck',
    ]);
    // Groceries: 500.00 (50% used)
    $user->transactions()->create([
        'category_id' => $expenseCat1->id,
        'amount' => '500.00',
        'transaction_date' => $today,
        'description' => 'Supermarket run',
    ]);
    // Rent: 1800.00 (90% used)
    $user->transactions()->create([
        'category_id' => $expenseCat2->id,
        'amount' => '1800.00',
        'transaction_date' => $today,
        'description' => 'Apartment rent',
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    // Income
    $response->assertSee('5,000.00');
    // Expenses = 2,300.00
    $response->assertSee('2,300.00');
    // Net = +2,700.00
    $response->assertSee('+2,700.00');
    // Budget Used: 2300 / 3000 = 76.67% -> 77%
    $response->assertSee('77%');
    // Top budget (Rent at 90%)
    $response->assertSee('Rent');
    $response->assertSee('Supermarket run');
    $response->assertSee('Apartment rent');
});
