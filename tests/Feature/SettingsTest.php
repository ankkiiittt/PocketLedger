<?php

declare(strict_types=1);

use App\Enums\CategoryType;
use App\Enums\Frequency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('user can view settings page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/settings');

    $response->assertStatus(200);
    $response->assertSee('Profile');
});

test('user can update profile and currency', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
        'currency_code' => 'INR',
    ]);

    $response = $this->actingAs($user)->put('/settings/profile', [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'currency_code' => 'USD',
    ]);

    $response->assertRedirect('/settings');
    $response->assertSessionHas('success');

    $user->refresh();
    expect($user->name)->toBe('Updated Name')
        ->and($user->email)->toBe('updated@example.com')
        ->and($user->currency_code)->toBe('USD');
});

test('changing password requires correct current password', function () {
    $user = User::factory()->create([
        'password' => 'current-password123',
    ]);

    $response = $this->actingAs($user)->put('/settings/password', [
        'current_password' => 'wrong-current-password',
        'password' => 'new-secure-password123',
        'password_confirmation' => 'new-secure-password123',
    ]);

    $response->assertSessionHasErrors('current_password');

    // With correct current password
    $successResponse = $this->actingAs($user)->put('/settings/password', [
        'current_password' => 'current-password123',
        'password' => 'new-secure-password123',
        'password_confirmation' => 'new-secure-password123',
    ]);

    $successResponse->assertRedirect('/settings');
    $successResponse->assertSessionHas('success');

    $user->refresh();
    expect(Hash::check('new-secure-password123', $user->password))->toBeTrue();
});

test('delete account removes all owned data in safe order and logs user out', function () {
    $user = User::factory()->create([
        'password' => 'my-password123',
    ]);

    // Create related categories, budgets, recurring transactions, transactions
    $category = $user->categories()->create([
        'name' => 'Groceries',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    $user->budgets()->create([
        'category_id' => $category->id,
        'month' => '2026-10-01',
        'amount_limit' => '1000.00',
    ]);

    $recurring = $user->recurringTransactions()->create([
        'category_id' => $category->id,
        'amount' => '100.00',
        'frequency' => Frequency::Monthly,
        'start_date' => '2026-10-01',
    ]);

    $user->transactions()->create([
        'category_id' => $category->id,
        'amount' => '50.00',
        'transaction_date' => '2026-10-18',
        'recurring_transaction_id' => $recurring->id,
    ]);

    $response = $this->actingAs($user)->delete('/settings/account', [
        'password' => 'my-password123',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionHas('success', 'Your account and data were deleted.');
    $this->assertGuest();

    // Verify all rows owned by the user are deleted
    expect(User::find($user->id))->toBeNull()
        ->and(DB::table('categories')->where('user_id', $user->id)->count())->toBe(0)
        ->and(DB::table('transactions')->where('user_id', $user->id)->count())->toBe(0)
        ->and(DB::table('budgets')->where('user_id', $user->id)->count())->toBe(0)
        ->and(DB::table('recurring_transactions')->where('user_id', $user->id)->count())->toBe(0);
});
