<?php

declare(strict_types=1);

use App\Enums\CategoryType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to login from reports and denied on json endpoints', function () {
    $this->get('/reports')->assertRedirect('/login');
    $this->get('/reports/data/category-breakdown')->assertRedirect('/login');
    $this->get('/reports/data/monthly-trend')->assertRedirect('/login');
});

test('reports page displays monthly summary with savings rate', function () {
    $user = User::factory()->create(['currency_code' => 'INR']);
    $expenseCat = $user->categories()->create([
        'name' => 'Groceries',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);
    $incomeCat = $user->categories()->create([
        'name' => 'Salary',
        'type' => CategoryType::Income,
        'color' => '#2E7D4F',
    ]);

    $monthDate = '2026-10-01';
    $monthYm = '2026-10';

    // 1. When income is zero -> savings rate is '-'
    $user->transactions()->create([
        'category_id' => $expenseCat->id,
        'amount' => '300.00',
        'transaction_date' => '2026-10-05',
    ]);

    $zeroIncomeResponse = $this->actingAs($user)->get("/reports?month={$monthYm}");
    $zeroIncomeResponse->assertStatus(200);
    $zeroIncomeResponse->assertSee('300.00');
    // Savings rate stat contains '-'
    $zeroIncomeResponse->assertSee('-');

    // 2. Add income: 1000.00 -> Net = 700.00 -> Savings rate = 70.0%
    $user->transactions()->create([
        'category_id' => $incomeCat->id,
        'amount' => '1000.00',
        'transaction_date' => '2026-10-01',
    ]);

    $incomeResponse = $this->actingAs($user)->get("/reports?month={$monthYm}");
    $incomeResponse->assertSee('70.0%');
});

test('reports page displays category breakdown with budget variance and top 5 expenses', function () {
    $user = User::factory()->create();
    $cat1 = $user->categories()->create([
        'name' => 'Groceries',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);
    $cat2 = $user->categories()->create([
        'name' => 'Rent',
        'type' => CategoryType::Expense,
        'color' => '#B4432F',
    ]);

    $monthDate = '2026-10-01';
    $monthYm = '2026-10';

    // Set budget for Groceries: limit 500, spent 300 -> variance +200
    $user->budgets()->create(['category_id' => $cat1->id, 'month' => $monthDate, 'amount_limit' => '500.00']);
    $user->transactions()->create([
        'category_id' => $cat1->id,
        'amount' => '300.00',
        'transaction_date' => '2026-10-05',
        'description' => 'Weekly grocery',
    ]);

    // Set budget for Rent: limit 1000, spent 1200 -> variance -200 (over budget)
    $user->budgets()->create(['category_id' => $cat2->id, 'month' => $monthDate, 'amount_limit' => '1000.00']);
    $user->transactions()->create([
        'category_id' => $cat2->id,
        'amount' => '1200.00',
        'transaction_date' => '2026-10-01',
        'description' => 'Apartment monthly',
    ]);

    $response = $this->actingAs($user)->get("/reports?month={$monthYm}");
    $response->assertStatus(200);

    // Breakdown table checks
    $response->assertSee('Groceries');
    $response->assertSee('300.00');
    $response->assertSee('+200.00');

    $response->assertSee('Rent');
    $response->assertSee('1,200.00');
    $response->assertSee('-200.00');

    // Top 5 expenses
    $response->assertSee('Apartment monthly');
    $response->assertSee('Weekly grocery');
});

test('category breakdown JSON endpoint conforms to Appendix C contract', function () {
    $user = User::factory()->create(['currency_code' => 'INR']);
    $cat = $user->categories()->create([
        'name' => 'Dining Out',
        'type' => CategoryType::Expense,
        'color' => '#B4432F',
    ]);

    $user->transactions()->create([
        'category_id' => $cat->id,
        'amount' => '450.00',
        'transaction_date' => '2026-10-15',
    ]);

    $response = $this->actingAs($user)->getJson('/reports/data/category-breakdown?month=2026-10');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'month',
        'currency',
        'total',
        'items' => [
            '*' => ['category_id', 'name', 'color', 'amount', 'percent'],
        ],
    ]);

    $data = $response->json();
    expect($data['month'])->toBe('2026-10')
        ->and($data['currency'])->toBe('INR')
        ->and($data['total'])->toBe('450.00')
        ->and($data['items'][0]['name'])->toBe('Dining Out')
        ->and($data['items'][0]['amount'])->toBe('450.00')
        ->and($data['items'][0]['percent'])->toEqual(100.0);
});

test('monthly trend JSON endpoint returns exactly 6 months with zero-fill', function () {
    $user = User::factory()->create(['currency_code' => 'USD']);
    $cat = $user->categories()->create([
        'name' => 'Salary',
        'type' => CategoryType::Income,
        'color' => '#2E7D4F',
    ]);

    // Transaction only in 2026-10
    $user->transactions()->create([
        'category_id' => $cat->id,
        'amount' => '3000.00',
        'transaction_date' => '2026-10-01',
    ]);

    $response = $this->actingAs($user)->getJson('/reports/data/monthly-trend?month=2026-10');

    $response->assertStatus(200);
    $data = $response->json();

    expect($data['currency'])->toBe('USD')
        ->and($data['months'])->toHaveLength(6)
        ->and($data['months'][5]['month'])->toBe('2026-10')
        ->and($data['months'][5]['income'])->toBe('3000.00')
        ->and($data['months'][5]['expense'])->toBe('0.00')
        // Earlier month without data is zero-filled
        ->and($data['months'][0]['month'])->toBe('2026-05')
        ->and($data['months'][0]['income'])->toBe('0.00')
        ->and($data['months'][0]['expense'])->toBe('0.00');
});

test('CSV export has UTF-8 BOM, header, neutralises formula injection, and validates date bounds', function () {
    $user = User::factory()->create();
    $cat = $user->categories()->create([
        'name' => 'Test Cat',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    // Create transaction with formula injection attempts: =cmd, +sum, -diff, @mention
    $user->transactions()->create([
        'category_id' => $cat->id,
        'amount' => '99.00',
        'transaction_date' => '2026-10-10',
        'description' => '=SUM(A1:A10)',
    ]);
    $user->transactions()->create([
        'category_id' => $cat->id,
        'amount' => '50.00',
        'transaction_date' => '2026-10-12',
        'description' => '@admin dangerous note',
    ]);

    // 1. Test validation: to date cannot exceed 5 years from from date
    $this->actingAs($user)->get('/reports/export?from=2020-01-01&to=2026-01-01')
        ->assertSessionHasErrors('to');

    // 2. Test valid export
    $response = $this->actingAs($user)->get('/reports/export?from=2026-10-01&to=2026-10-31');

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    $content = $response->streamedContent();

    // Check UTF-8 BOM
    expect(str_starts_with($content, "\xEF\xBB\xBF"))->toBeTrue();

    // Check header
    expect($content)->toContain('date,type,category,amount,description');

    // Check formula injection prefix: =SUM... must be prefixed with '
    expect($content)->toContain("'=SUM(A1:A10)");
    expect($content)->toContain("'@admin dangerous note");
});
