<?php

declare(strict_types=1);

use App\Enums\Frequency;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Services\RecurringTransactionProcessor;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to login from recurring routes', function () {
    $this->get('/recurring')->assertRedirect(route('login'));
    $this->get('/recurring/create')->assertRedirect(route('login'));
    $this->post('/recurring', [])->assertRedirect(route('login'));
    $this->post('/recurring/process-due')->assertRedirect(route('login'));
});

test('user can view recurring index with empty state', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('recurring.index'))
        ->assertOk()
        ->assertSee('No recurring transactions')
        ->assertSee('Add one for rent, salary or subscriptions.');
});

test('user can view recurring index with their rules', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->expense()->create(['name' => 'Rent']);

    $rule = RecurringTransaction::factory()->for($user)->for($category)->create([
        'description' => 'Monthly Apartment Rent',
        'amount' => '15000.00',
        'frequency' => Frequency::Monthly,
        'start_date' => '2026-10-01',
        'next_run_date' => '2026-11-01',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('recurring.index'))
        ->assertOk()
        ->assertSee('Monthly Apartment Rent')
        ->assertSee('15,000.00')
        ->assertSee('Active')
        ->assertSee('01 Nov 2026');
});

test('user can create a recurring rule', function () {
    Carbon::setTestNow('2026-10-18');

    $user = User::factory()->create();
    $category = Category::factory()->for($user)->expense()->create();

    $response = $this->actingAs($user)->post(route('recurring.store'), [
        'category_id' => $category->id,
        'amount' => '450.00',
        'frequency' => 'monthly',
        'start_date' => '2026-10-18',
        'end_date' => '2027-10-18',
        'description' => 'Gym membership',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('recurring.index'))
        ->assertSessionHas('success', 'Recurring rule created.');

    $this->assertDatabaseHas('recurring_transactions', [
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => '450.00',
        'description' => 'Gym membership',
        'frequency' => 'monthly',
        'start_date' => '2026-10-18',
        'end_date' => '2027-10-18',
        'next_run_date' => '2026-10-18',
        'runs_count' => 0,
        'is_active' => 1,
    ]);
});

test('validation rejects invalid recurring rule data', function () {
    Carbon::setTestNow('2026-10-18');

    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherCategory = Category::factory()->for($otherUser)->create();
    $archivedCategory = Category::factory()->for($user)->archived()->create();

    // Start date in past on create
    $this->actingAs($user)
        ->post(route('recurring.store'), [
            'category_id' => $archivedCategory->id,
            'amount' => '100.00',
            'frequency' => 'monthly',
            'start_date' => '2026-10-10', // past
        ])
        ->assertSessionHasErrors(['start_date', 'category_id']);

    // Foreign category
    $this->actingAs($user)
        ->post(route('recurring.store'), [
            'category_id' => $otherCategory->id,
            'amount' => '100.00',
            'frequency' => 'monthly',
            'start_date' => '2026-10-18',
        ])
        ->assertSessionHasErrors(['category_id']);

    // End date before start date
    $this->actingAs($user)
        ->post(route('recurring.store'), [
            'category_id' => Category::factory()->for($user)->create()->id,
            'amount' => '100.00',
            'frequency' => 'monthly',
            'start_date' => '2026-10-20',
            'end_date' => '2026-10-19',
        ])
        ->assertSessionHasErrors(['end_date']);
});

test('user can update recurring rule', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->expense()->create();
    $newCategory = Category::factory()->for($user)->expense()->create();

    $rule = RecurringTransaction::factory()->for($user)->for($category)->create([
        'amount' => '500.00',
        'description' => 'Original',
        'frequency' => Frequency::Monthly,
        'start_date' => '2026-01-01',
    ]);

    $response = $this->actingAs($user)->put(route('recurring.update', $rule), [
        'category_id' => $newCategory->id,
        'amount' => '650.00',
        'description' => 'Updated Description',
        'frequency' => 'monthly',
        'start_date' => '2026-01-01',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('recurring.index'))
        ->assertSessionHas('success', 'Recurring rule updated.');

    $rule->refresh();
    expect($rule->amount)->toBe('650.00')
        ->and($rule->description)->toBe('Updated Description')
        ->and($rule->category_id)->toBe($newCategory->id);
});

test('user can pause and resume a recurring rule', function () {
    Carbon::setTestNow('2026-10-18');

    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create();

    $rule = RecurringTransaction::factory()->for($user)->for($category)->create([
        'is_active' => true,
        'frequency' => Frequency::Monthly,
        'start_date' => '2026-01-01',
        'runs_count' => 9,
        'next_run_date' => '2026-10-01',
    ]);

    // 1. Pause
    $this->actingAs($user)
        ->patch(route('recurring.toggle', $rule))
        ->assertRedirect(route('recurring.index'))
        ->assertSessionHas('success', 'Rule paused.');

    $rule->refresh();
    expect($rule->is_active)->toBeFalse();

    // 2. Resume
    // Next occurrence on or after 2026-10-18 for monthly starting 2026-01-01:
    // 2026-11-01 (n=10)
    $this->actingAs($user)
        ->patch(route('recurring.toggle', $rule))
        ->assertRedirect(route('recurring.index'))
        ->assertSessionHas('success', 'Rule resumed.');

    $rule->refresh();
    expect($rule->is_active)->toBeTrue()
        ->and($rule->runs_count)->toBe(10)
        ->and($rule->next_run_date?->toDateString())->toBe('2026-11-01');
});

test('deleting a rule keeps already generated transactions', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create();

    $rule = RecurringTransaction::factory()->for($user)->for($category)->create();
    $txn = Transaction::factory()->for($user)->for($category)->create([
        'recurring_transaction_id' => $rule->id,
    ]);

    $this->actingAs($user)
        ->delete(route('recurring.destroy', $rule))
        ->assertRedirect(route('recurring.index'))
        ->assertSessionHas('success', 'Recurring rule deleted.');

    $this->assertDatabaseMissing('recurring_transactions', ['id' => $rule->id]);
    $this->assertDatabaseHas('transactions', [
        'id' => $txn->id,
        'recurring_transaction_id' => null, // ON DELETE SET NULL
    ]);
});

test('foreign recurring transaction returns 404', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $foreignRule = RecurringTransaction::factory()->for($otherUser)->create();

    $this->actingAs($user)->get(route('recurring.edit', $foreignRule))->assertNotFound();
    $this->actingAs($user)->put(route('recurring.update', $foreignRule), [
        'category_id' => $foreignRule->category_id,
        'amount' => '100.00',
        'frequency' => 'monthly',
        'start_date' => '2026-01-01',
    ])->assertNotFound();
    $this->actingAs($user)->patch(route('recurring.toggle', $foreignRule))->assertNotFound();
    $this->actingAs($user)->delete(route('recurring.destroy', $foreignRule))->assertNotFound();
});

test('process-due button creates due transactions for current user only', function () {
    Carbon::setTestNow('2026-10-18');

    $user = User::factory()->create();
    $cat = Category::factory()->for($user)->create();
    $dueRule = RecurringTransaction::factory()->for($user)->for($cat)->create([
        'frequency' => Frequency::Daily,
        'start_date' => '2026-10-17',
        'next_run_date' => '2026-10-17',
        'runs_count' => 0,
        'is_active' => true,
    ]);

    $otherUser = User::factory()->create();
    $otherCat = Category::factory()->for($otherUser)->create();
    $otherRule = RecurringTransaction::factory()->for($otherUser)->for($otherCat)->create([
        'frequency' => Frequency::Daily,
        'start_date' => '2026-10-17',
        'next_run_date' => '2026-10-17',
        'runs_count' => 0,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->post(route('recurring.process'));
    $response->assertRedirect(route('recurring.index'))
        ->assertSessionHas('success', 'Created 2 transactions.'); // 2026-10-17 and 2026-10-18

    // Other user's rule was NOT processed
    $otherRule->refresh();
    expect($otherRule->runs_count)->toBe(0);

    // Second run says "Nothing is due."
    $this->actingAs($user)
        ->post(route('recurring.process'))
        ->assertRedirect(route('recurring.index'))
        ->assertSessionHas('info', 'Nothing is due.');
});

test('catch-up processes missed days and double run is idempotent', function () {
    Carbon::setTestNow('2026-10-05');

    $user = User::factory()->create();
    $cat = Category::factory()->for($user)->create();

    $rule = RecurringTransaction::factory()->for($user)->for($cat)->create([
        'frequency' => Frequency::Daily,
        'start_date' => '2026-10-01',
        'next_run_date' => '2026-10-01',
        'runs_count' => 0,
        'is_active' => true,
    ]);

    $processor = app(RecurringTransactionProcessor::class);
    $createdCount = $processor->processAllDue();

    // From Oct 1 to Oct 5 = 5 days
    expect($createdCount)->toBe(5);
    expect(Transaction::where('recurring_transaction_id', $rule->id)->count())->toBe(5);

    $rule->refresh();
    expect($rule->runs_count)->toBe(5)
        ->and($rule->last_run_date?->toDateString())->toBe('2026-10-05')
        ->and($rule->next_run_date?->toDateString())->toBe('2026-10-06');

    // Running again on the same day creates 0
    $secondCount = $processor->processAllDue();
    expect($secondCount)->toBe(0);
    expect(Transaction::where('recurring_transaction_id', $rule->id)->count())->toBe(5);
});

test('end date stops occurrence creation and marks rule ended', function () {
    Carbon::setTestNow('2026-10-05');

    $user = User::factory()->create();
    $cat = Category::factory()->for($user)->create();

    $rule = RecurringTransaction::factory()->for($user)->for($cat)->create([
        'frequency' => Frequency::Daily,
        'start_date' => '2026-10-01',
        'end_date' => '2026-10-03',
        'next_run_date' => '2026-10-01',
        'runs_count' => 0,
        'is_active' => true,
    ]);

    $processor = app(RecurringTransactionProcessor::class);
    $processor->processAllDue();

    // Only 3 transactions created: Oct 1, 2, 3
    expect(Transaction::where('recurring_transaction_id', $rule->id)->count())->toBe(3);

    $rule->refresh();
    expect($rule->next_run_date)->toBeNull()
        ->and($rule->status())->toBe('Ended');
});
