<?php

declare(strict_types=1);

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to login from budgets page', function () {
    $this->get('/budgets')->assertRedirect('/login');
});

test('user sees empty state when having no expense categories', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/budgets');

    $response->assertStatus(200);
    $response->assertSee('No expense categories found');
});

test('user can bulk save budgets and blank limit removes budget', function () {
    $user = User::factory()->create();
    $cat1 = $user->categories()->create([
        'name' => 'Groceries',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);
    $cat2 = $user->categories()->create([
        'name' => 'Dining Out',
        'type' => CategoryType::Expense,
        'color' => '#B4432F',
    ]);

    $currentMonth = Carbon::now()->format('Y-m');
    $monthDate = Carbon::now()->startOfMonth()->toDateString();

    // 1. Bulk save budgets for cat1 and cat2
    $response = $this->actingAs($user)->put('/budgets', [
        'month' => $currentMonth,
        'limits' => [
            $cat1->id => '500.00',
            $cat2->id => '200.00',
        ],
    ]);

    $response->assertRedirect("/budgets?month={$currentMonth}");
    $response->assertSessionHas('success', 'Budgets saved.');

    $this->assertDatabaseHas('budgets', [
        'user_id' => $user->id,
        'category_id' => $cat1->id,
        'month' => $monthDate,
        'amount_limit' => '500.00',
    ]);
    $this->assertDatabaseHas('budgets', [
        'user_id' => $user->id,
        'category_id' => $cat2->id,
        'month' => $monthDate,
        'amount_limit' => '200.00',
    ]);

    // 2. Blank out cat2 limit -> should delete cat2 budget
    $updateResponse = $this->actingAs($user)->put('/budgets', [
        'month' => $currentMonth,
        'limits' => [
            $cat1->id => '600.00',
            $cat2->id => '',
        ],
    ]);

    $updateResponse->assertRedirect("/budgets?month={$currentMonth}");

    $this->assertDatabaseHas('budgets', [
        'user_id' => $user->id,
        'category_id' => $cat1->id,
        'month' => $monthDate,
        'amount_limit' => '600.00',
    ]);
    $this->assertDatabaseMissing('budgets', [
        'user_id' => $user->id,
        'category_id' => $cat2->id,
        'month' => $monthDate,
    ]);
});

test('bulk save rejects income categories and foreign categories', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $incomeCat = $user1->categories()->create([
        'name' => 'Salary',
        'type' => CategoryType::Income,
        'color' => '#2E7D4F',
    ]);

    $foreignCat = $user2->categories()->create([
        'name' => 'Secret Expense',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    $currentMonth = Carbon::now()->format('Y-m');

    // Attempt to set budget on income category
    $this->actingAs($user1)->put('/budgets', [
        'month' => $currentMonth,
        'limits' => [
            $incomeCat->id => '1000.00',
        ],
    ])->assertSessionHasErrors('limits');

    // Attempt to set budget on foreign category
    $this->actingAs($user1)->put('/budgets', [
        'month' => $currentMonth,
        'limits' => [
            $foreignCat->id => '500.00',
        ],
    ])->assertSessionHasErrors('limits');
});

test('month bounds are enforced between 2020-01 and current month plus 12', function () {
    $user = User::factory()->create();
    $cat = $user->categories()->create([
        'name' => 'Food',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    // Month before 2020-01
    $this->actingAs($user)->put('/budgets', [
        'month' => '2019-12',
        'limits' => [
            $cat->id => '100.00',
        ],
    ])->assertSessionHasErrors('month');

    // Month more than 12 months in the future
    $tooFarFuture = Carbon::now()->addMonths(14)->format('Y-m');
    $this->actingAs($user)->put('/budgets', [
        'month' => $tooFarFuture,
        'limits' => [
            $cat->id => '100.00',
        ],
    ])->assertSessionHasErrors('month');
});

test('copy from previous month copies limits, does not overwrite, and skips archived', function () {
    $user = User::factory()->create();
    $activeCat1 = $user->categories()->create([
        'name' => 'Active 1',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);
    $activeCat2 = $user->categories()->create([
        'name' => 'Active 2',
        'type' => CategoryType::Expense,
        'color' => '#B4432F',
    ]);
    $archivedCat = $user->categories()->create([
        'name' => 'Archived Cat',
        'type' => CategoryType::Expense,
        'color' => '#2F5F8A',
        'is_archived' => true,
    ]);

    $prevMonthYm = '2026-05';
    $thisMonthYm = '2026-06';
    $prevMonthDate = '2026-05-01';
    $thisMonthDate = '2026-06-01';

    // Set budgets in prevMonth for all 3 categories
    $user->budgets()->create(['category_id' => $activeCat1->id, 'month' => $prevMonthDate, 'amount_limit' => '300.00']);
    $user->budgets()->create(['category_id' => $activeCat2->id, 'month' => $prevMonthDate, 'amount_limit' => '400.00']);
    $user->budgets()->create(['category_id' => $archivedCat->id, 'month' => $prevMonthDate, 'amount_limit' => '500.00']);

    // In thisMonth, activeCat1 ALREADY has a budget of 350.00
    $user->budgets()->create(['category_id' => $activeCat1->id, 'month' => $thisMonthDate, 'amount_limit' => '350.00']);

    // Execute copy
    $response = $this->actingAs($user)->post('/budgets/copy-previous', [
        'month' => $thisMonthYm,
    ]);

    $response->assertRedirect("/budgets?month={$thisMonthYm}");
    $response->assertSessionHas('success', 'Copied 1 budget.');

    // activeCat1 should remain 350.00 (NOT overwritten by 300.00)
    $this->assertDatabaseHas('budgets', [
        'user_id' => $user->id,
        'category_id' => $activeCat1->id,
        'month' => $thisMonthDate,
        'amount_limit' => '350.00',
    ]);

    // activeCat2 should have been copied with 400.00
    $this->assertDatabaseHas('budgets', [
        'user_id' => $user->id,
        'category_id' => $activeCat2->id,
        'month' => $thisMonthDate,
        'amount_limit' => '400.00',
    ]);

    // archivedCat should NOT be copied because it is archived
    $this->assertDatabaseMissing('budgets', [
        'user_id' => $user->id,
        'category_id' => $archivedCat->id,
        'month' => $thisMonthDate,
    ]);

    // Running copy a second time should copy 0 budgets
    $secondResponse = $this->actingAs($user)->post('/budgets/copy-previous', [
        'month' => $thisMonthYm,
    ]);
    $secondResponse->assertSessionHas('info', 'Nothing to copy.');
});

test('status thresholds display On track, Nearing limit, and Over budget correctly', function () {
    $user = User::factory()->create();
    $cat = $user->categories()->create([
        'name' => 'Thresholds',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    $monthDate = '2026-07-01';
    $monthYm = '2026-07';

    // Budget limit = 1000.00
    $user->budgets()->create([
        'category_id' => $cat->id,
        'month' => $monthDate,
        'amount_limit' => '1000.00',
    ]);

    // 1. Spent 799.90 (79.99%) -> On track
    $txn = $user->transactions()->create([
        'category_id' => $cat->id,
        'amount' => '799.90',
        'transaction_date' => '2026-07-10',
    ]);

    $res1 = $this->actingAs($user)->get("/budgets?month={$monthYm}");
    $res1->assertSee('On track (80%)');

    // 2. Spent 800.00 (80.00%) -> Nearing limit
    $txn->update(['amount' => '800.00']);
    $res2 = $this->actingAs($user)->get("/budgets?month={$monthYm}");
    $res2->assertSee('Nearing limit (80%)');

    // 3. Spent 1000.00 (100.00%) -> Nearing limit
    $txn->update(['amount' => '1000.00']);
    $res3 = $this->actingAs($user)->get("/budgets?month={$monthYm}");
    $res3->assertSee('Nearing limit (100%)');

    // 4. Spent 1000.10 (100.01%) -> Over budget
    $txn->update(['amount' => '1000.10']);
    $res4 = $this->actingAs($user)->get("/budgets?month={$monthYm}");
    $res4->assertSee('Over budget (100%)');
});
