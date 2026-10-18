<?php

declare(strict_types=1);

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('guests are redirected to login from transactions pages', function () {
    $this->get('/transactions')->assertRedirect('/login');
    $this->get('/transactions/create')->assertRedirect('/login');
});

test('user sees first-use empty state when having no transactions', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/transactions');

    $response->assertStatus(200);
    $response->assertSee('No transactions yet');
    $response->assertSee('Add transaction');
});

test('user can create an expense transaction without budget with Transaction saved flash', function () {
    $user = User::factory()->create();
    $category = $user->categories()->create([
        'name' => 'Transport',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    $response = $this->actingAs($user)->post('/transactions', [
        'category_id' => $category->id,
        'amount' => '45.50',
        'transaction_date' => Carbon::today()->toDateString(),
        'description' => 'Bus fare',
    ]);

    $response->assertRedirect('/transactions');
    $response->assertSessionHas('success', 'Transaction saved.');

    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => '45.50',
        'description' => 'Bus fare',
    ]);
});

test('user can create an expense transaction with budget showing budget status message', function () {
    $user = User::factory()->create();
    $category = $user->categories()->create([
        'name' => 'Groceries',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    $today = Carbon::today();
    $monthStart = $today->copy()->startOfMonth()->toDateString();

    $user->budgets()->create([
        'category_id' => $category->id,
        'month' => $monthStart,
        'amount_limit' => '1000.00',
    ]);

    // Create first transaction of 250.00 -> 25% used
    $response = $this->actingAs($user)->post('/transactions', [
        'category_id' => $category->id,
        'amount' => '250.00',
        'transaction_date' => $today->toDateString(),
        'description' => 'Supermarket',
    ]);

    $response->assertRedirect('/transactions');

    $expectedMonth = $today->format('M Y');
    // Saved. Groceries: 250.00 of 1,000.00 used in Oct 2026 (25%).
    $response->assertSessionHas('success', function ($msg) use ($expectedMonth) {
        return str_contains($msg, 'Saved. Groceries:') &&
            str_contains($msg, '250.00 of 1,000.00 used in '.$expectedMonth.' (25%).');
    });
});

test('save and add another redirects back to create form with flash', function () {
    $user = User::factory()->create();
    $category = $user->categories()->create([
        'name' => 'Groceries',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    $response = $this->actingAs($user)->post('/transactions', [
        'category_id' => $category->id,
        'amount' => '50.00',
        'transaction_date' => Carbon::today()->toDateString(),
        'action' => 'add_another',
    ]);

    $response->assertRedirect('/transactions/create');
    $response->assertSessionHas('success', 'Transaction saved.');
});

test('validation rejects invalid amounts and future dates', function () {
    $user = User::factory()->create();
    $category = $user->categories()->create([
        'name' => 'Groceries',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    // 0.00
    $this->actingAs($user)->post('/transactions', [
        'category_id' => $category->id,
        'amount' => '0.00',
        'transaction_date' => Carbon::today()->toDateString(),
    ])->assertSessionHasErrors('amount');

    // 3 decimal places
    $this->actingAs($user)->post('/transactions', [
        'category_id' => $category->id,
        'amount' => '10.123',
        'transaction_date' => Carbon::today()->toDateString(),
    ])->assertSessionHasErrors('amount');

    // Negative amount
    $this->actingAs($user)->post('/transactions', [
        'category_id' => $category->id,
        'amount' => '-25.00',
        'transaction_date' => Carbon::today()->toDateString(),
    ])->assertSessionHasErrors('amount');

    // Future date
    $this->actingAs($user)->post('/transactions', [
        'category_id' => $category->id,
        'amount' => '50.00',
        'transaction_date' => Carbon::tomorrow()->toDateString(),
    ])->assertSessionHasErrors('transaction_date');

    // Date before 2000-01-01
    $this->actingAs($user)->post('/transactions', [
        'category_id' => $category->id,
        'amount' => '50.00',
        'transaction_date' => '1999-12-31',
    ])->assertSessionHasErrors('transaction_date');
});

test('validation rejects archived and foreign categories on creation', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $archivedCategory = $user1->categories()->create([
        'name' => 'Archived Cat',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
        'is_archived' => true,
    ]);

    $foreignCategory = $user2->categories()->create([
        'name' => 'Foreign Cat',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
        'is_archived' => false,
    ]);

    // Archived category
    $this->actingAs($user1)->post('/transactions', [
        'category_id' => $archivedCategory->id,
        'amount' => '20.00',
        'transaction_date' => Carbon::today()->toDateString(),
    ])->assertSessionHasErrors('category_id');

    // Foreign category
    $this->actingAs($user1)->post('/transactions', [
        'category_id' => $foreignCategory->id,
        'amount' => '20.00',
        'transaction_date' => Carbon::today()->toDateString(),
    ])->assertSessionHasErrors('category_id');
});

test('editing transaction allows keeping an archived category but rejects foreign category', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $category = $user1->categories()->create([
        'name' => 'Old Category',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
        'is_archived' => false,
    ]);

    $transaction = $user1->transactions()->create([
        'category_id' => $category->id,
        'amount' => '100.00',
        'transaction_date' => Carbon::today()->toDateString(),
    ]);

    // Archive the category after transaction was created
    $category->update(['is_archived' => true]);

    // Updating transaction while keeping its archived category should succeed
    $response = $this->actingAs($user1)->put("/transactions/{$transaction->id}", [
        'category_id' => $category->id,
        'amount' => '120.00',
        'transaction_date' => Carbon::today()->toDateString(),
        'description' => 'Updated note',
    ]);

    $response->assertRedirect('/transactions');
    $response->assertSessionHas('success', 'Transaction updated.');

    // Attempting to change to foreign category should fail
    $foreignCategory = $user2->categories()->create([
        'name' => 'Foreign',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    $failResponse = $this->actingAs($user1)->put("/transactions/{$transaction->id}", [
        'category_id' => $foreignCategory->id,
        'amount' => '120.00',
        'transaction_date' => Carbon::today()->toDateString(),
    ]);

    $failResponse->assertSessionHasErrors('category_id');
});

test('return_to redirects safely and prevents open redirects', function () {
    $user = User::factory()->create();
    $category = $user->categories()->create([
        'name' => 'Food',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    $transaction = $user->transactions()->create([
        'category_id' => $category->id,
        'amount' => '30.00',
        'transaction_date' => Carbon::today()->toDateString(),
    ]);

    // Valid internal return_to
    $validResponse = $this->actingAs($user)->put("/transactions/{$transaction->id}", [
        'category_id' => $category->id,
        'amount' => '35.00',
        'transaction_date' => Carbon::today()->toDateString(),
        'return_to' => '/transactions?page=2',
    ]);

    $validResponse->assertRedirect('/transactions?page=2');

    // Malicious open redirect attempt
    $evilResponse = $this->actingAs($user)->put("/transactions/{$transaction->id}", [
        'category_id' => $category->id,
        'amount' => '40.00',
        'transaction_date' => Carbon::today()->toDateString(),
        'return_to' => 'https://evil.com',
    ]);

    $evilResponse->assertRedirect('/transactions');
});

test('user can delete transaction', function () {
    $user = User::factory()->create();
    $category = $user->categories()->create([
        'name' => 'Food',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    $transaction = $user->transactions()->create([
        'category_id' => $category->id,
        'amount' => '30.00',
        'transaction_date' => Carbon::today()->toDateString(),
    ]);

    $response = $this->actingAs($user)->delete("/transactions/{$transaction->id}");

    $response->assertRedirect('/transactions');
    $response->assertSessionHas('success', 'Transaction deleted.');
    $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
});

test('foreign transaction returns 404 for edit, update, delete', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $category = $user1->categories()->create([
        'name' => 'Food',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    $transaction = $user1->transactions()->create([
        'category_id' => $category->id,
        'amount' => '30.00',
        'transaction_date' => Carbon::today()->toDateString(),
    ]);

    $this->actingAs($user2)->get("/transactions/{$transaction->id}/edit")->assertNotFound();

    $this->actingAs($user2)->put("/transactions/{$transaction->id}", [
        'category_id' => $category->id,
        'amount' => '50.00',
        'transaction_date' => Carbon::today()->toDateString(),
    ])->assertNotFound();

    $this->actingAs($user2)->delete("/transactions/{$transaction->id}")->assertNotFound();
});

test('filters and search properly filter transactions with search escaping', function () {
    $user = User::factory()->create();
    $catExpense = $user->categories()->create([
        'name' => 'Snacks',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);
    $catIncome = $user->categories()->create([
        'name' => 'Salary',
        'type' => CategoryType::Income,
        'color' => '#2E7D4F',
    ]);

    $t1 = $user->transactions()->create([
        'category_id' => $catExpense->id,
        'amount' => '100.00',
        'transaction_date' => '2026-05-10',
        'description' => '100% juice discount',
    ]);
    $t2 = $user->transactions()->create([
        'category_id' => $catExpense->id,
        'amount' => '50.00',
        'transaction_date' => '2026-05-15',
        'description' => 'Regular juice',
    ]);
    $t3 = $user->transactions()->create([
        'category_id' => $catIncome->id,
        'amount' => '2000.00',
        'transaction_date' => '2026-05-01',
        'description' => 'May salary',
    ]);

    // Search for literal '100%' should only find t1, not t2 or t3
    $response = $this->actingAs($user)->get('/transactions?from=2026-05-01&to=2026-05-31&q=100%');
    $response->assertSee('100% juice discount');
    $response->assertDontSee('Regular juice');

    // Filter by type: income
    $incomeResponse = $this->actingAs($user)->get('/transactions?from=2026-05-01&to=2026-05-31&type=income');
    $incomeResponse->assertSee('May salary');
    $incomeResponse->assertDontSee('juice');
});

test('totals strip calculates income, expenses, and net across all pages', function () {
    $user = User::factory()->create();
    $catExpense = $user->categories()->create([
        'name' => 'Shopping',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);
    $catIncome = $user->categories()->create([
        'name' => 'Salary',
        'type' => CategoryType::Income,
        'color' => '#2E7D4F',
    ]);

    // Create 25 transactions so pagination kicks in (> 20 per page)
    for ($i = 0; $i < 24; $i++) {
        $user->transactions()->create([
            'category_id' => $catExpense->id,
            'amount' => '10.00',
            'transaction_date' => '2026-06-15',
        ]);
    }
    // Income transaction
    $user->transactions()->create([
        'category_id' => $catIncome->id,
        'amount' => '500.00',
        'transaction_date' => '2026-06-01',
    ]);

    // Total expense = 240.00, Total income = 500.00, Net = 260.00
    $response = $this->actingAs($user)->get('/transactions?from=2026-06-01&to=2026-06-30&page=2');

    $response->assertStatus(200);
    // Assert totals reflect the full 25 items, not just the 5 items on page 2
    $response->assertSee('+500.00');
    $response->assertSee('-240.00');
    $response->assertSee('+260.00');
});

test('query count remains constant regardless of row count on transactions list', function () {
    $user = User::factory()->create();
    $category = $user->categories()->create([
        'name' => 'Bills',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    // Seed 5 transactions
    for ($i = 0; $i < 5; $i++) {
        $user->transactions()->create([
            'category_id' => $category->id,
            'amount' => '20.00',
            'transaction_date' => '2026-07-01',
        ]);
    }

    DB::flushQueryLog();
    DB::enableQueryLog();

    $this->actingAs($user)->get('/transactions?from=2026-07-01&to=2026-07-31');
    $queries5 = count(DB::getQueryLog());

    // Seed 15 more transactions (total 20 on page)
    for ($i = 0; $i < 15; $i++) {
        $user->transactions()->create([
            'category_id' => $category->id,
            'amount' => '20.00',
            'transaction_date' => '2026-07-01',
        ]);
    }

    DB::flushQueryLog();
    $this->actingAs($user)->get('/transactions?from=2026-07-01&to=2026-07-31');
    $queries20 = count(DB::getQueryLog());

    expect($queries20)->toBe($queries5);
});

test('requesting page beyond last page redirects to last page', function () {
    $user = User::factory()->create();
    $category = $user->categories()->create([
        'name' => 'Bills',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    for ($i = 0; $i < 25; $i++) {
        $user->transactions()->create([
            'category_id' => $category->id,
            'amount' => '10.00',
            'transaction_date' => '2026-08-01',
        ]);
    }

    // Page 10 is way beyond page 2
    $response = $this->actingAs($user)->get('/transactions?from=2026-08-01&to=2026-08-31&page=10');

    $response->assertRedirect('/transactions?from=2026-08-01&to=2026-08-31&page=2');
});

test('transactions list page responds quickly with large dataset using indexes', function () {
    $user = User::factory()->create();
    $category = $user->categories()->create([
        'name' => 'General',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    $rows = [];
    $today = Carbon::today()->toDateString();
    for ($i = 0; $i < 5000; $i++) {
        $rows[] = [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => '15.00',
            'transaction_date' => $today,
            'description' => "Item {$i}",
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    foreach (array_chunk($rows, 1000) as $chunk) {
        DB::table('transactions')->insert($chunk);
    }

    $startTime = microtime(true);
    $response = $this->actingAs($user)->get('/transactions');
    $duration = microtime(true) - $startTime;

    $response->assertStatus(200);
    expect($duration)->toBeLessThan(1.0);
});
