<?php

declare(strict_types=1);

use App\Enums\CategoryType;
use App\Enums\Frequency;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to login from categories pages', function () {
    $this->get('/categories')->assertRedirect('/login');
    $this->get('/categories/create')->assertRedirect('/login');
});

test('user can view categories index with expense and income sections', function () {
    $user = User::factory()->create();
    $expense = $user->categories()->create([
        'name' => 'Groceries',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);
    $income = $user->categories()->create([
        'name' => 'Salary',
        'type' => CategoryType::Income,
        'color' => '#2E7D4F',
    ]);

    $response = $this->actingAs($user)->get('/categories');

    $response->assertStatus(200);
    $response->assertSee('Groceries');
    $response->assertSee('Salary');
    $response->assertSee('Expense Categories');
    $response->assertSee('Income Categories');
});

test('user can view create category page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/categories/create');

    $response->assertStatus(200);
    $response->assertSee('Add category');
    $response->assertSee('Category colour');
});

test('user can create a category with valid data and preset color', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/categories', [
        'name' => 'Dining Out',
        'type' => 'expense',
        'color' => '#B4432F',
    ]);

    $response->assertRedirect('/categories');
    $response->assertSessionHas('success', 'Category created.');

    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'name' => 'Dining Out',
        'type' => 'expense',
        'color' => '#B4432F',
        'is_archived' => 0,
    ]);
});

test('validation rejects invalid category input', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/categories', [
        'name' => '',
        'type' => 'invalid_type',
        'color' => '#FFFFFF', // not in preset 8
    ]);

    $response->assertSessionHasErrors(['name', 'type', 'color']);
});

test('category name is unique per user and type case insensitively', function () {
    $user = User::factory()->create();
    $user->categories()->create([
        'name' => 'Groceries',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    $response = $this->actingAs($user)->post('/categories', [
        'name' => 'groceries',
        'type' => 'expense',
        'color' => '#B4432F',
    ]);

    $response->assertSessionHasErrors('name');
});

test('different users can have categories with the same name', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $user1->categories()->create([
        'name' => 'Utilities',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    $response = $this->actingAs($user2)->post('/categories', [
        'name' => 'Utilities',
        'type' => 'expense',
        'color' => '#1F5F4D',
    ]);

    $response->assertRedirect('/categories');
    $this->assertDatabaseHas('categories', [
        'user_id' => $user2->id,
        'name' => 'Utilities',
    ]);
});

test('same user can have category with same name for different types', function () {
    $user = User::factory()->create();

    $user->categories()->create([
        'name' => 'Bonus',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    $response = $this->actingAs($user)->post('/categories', [
        'name' => 'Bonus',
        'type' => 'income',
        'color' => '#2F5F8A',
    ]);

    $response->assertRedirect('/categories');
    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'name' => 'Bonus',
        'type' => 'income',
    ]);
});

test('user can view edit category page', function () {
    $user = User::factory()->create();
    $category = $user->categories()->create([
        'name' => 'Groceries',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    $response = $this->actingAs($user)->get("/categories/{$category->id}/edit");

    $response->assertStatus(200);
    $response->assertSee('Edit category');
    $response->assertSee('Groceries');
});

test('user can update category name and color', function () {
    $user = User::factory()->create();
    $category = $user->categories()->create([
        'name' => 'Old Name',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    $response = $this->actingAs($user)->put("/categories/{$category->id}", [
        'name' => 'New Name',
        'type' => 'expense',
        'color' => '#2F5F8A',
    ]);

    $response->assertRedirect('/categories');
    $response->assertSessionHas('success', 'Category updated.');

    $category->refresh();
    expect($category->name)->toBe('New Name')
        ->and($category->color)->toBe('#2F5F8A');
});

test('updating category ignores self in unique rule', function () {
    $user = User::factory()->create();
    $category = $user->categories()->create([
        'name' => 'Groceries',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    $response = $this->actingAs($user)->put("/categories/{$category->id}", [
        'name' => 'Groceries',
        'type' => 'expense',
        'color' => '#B4432F',
    ]);

    $response->assertRedirect('/categories');
    $response->assertSessionHasNoErrors();
});

test('cannot change category type when category is used with transactions, budgets, or recurring', function () {
    $user = User::factory()->create();
    $category = $user->categories()->create([
        'name' => 'Rent',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    // Add transaction to use category
    $user->transactions()->create([
        'category_id' => $category->id,
        'amount' => '500.00',
        'transaction_date' => '2026-10-18',
    ]);

    $response = $this->actingAs($user)->put("/categories/{$category->id}", [
        'name' => 'Rent',
        'type' => 'income',
        'color' => '#1F5F4D',
    ]);

    $response->assertSessionHasErrors('type');

    $category->refresh();
    expect($category->type)->toBe(CategoryType::Expense);
});

test('user can archive and restore a category', function () {
    $user = User::factory()->create();
    $category = $user->categories()->create([
        'name' => 'Old Gym',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
        'is_archived' => false,
    ]);

    // Archive
    $archiveResponse = $this->actingAs($user)->patch("/categories/{$category->id}/archive");
    $archiveResponse->assertRedirect('/categories');
    $archiveResponse->assertSessionHas('success', 'Category archived.');

    $category->refresh();
    expect($category->is_archived)->toBeTrue();

    // Restore
    $restoreResponse = $this->actingAs($user)->patch("/categories/{$category->id}/restore");
    $restoreResponse->assertRedirect('/categories');
    $restoreResponse->assertSessionHas('success', 'Category restored.');

    $category->refresh();
    expect($category->is_archived)->toBeFalse();
});

test('archived categories are hidden by default and shown with show_archived=1', function () {
    $user = User::factory()->create();
    $active = $user->categories()->create([
        'name' => 'Active Category',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
        'is_archived' => false,
    ]);
    $archived = $user->categories()->create([
        'name' => 'Archived Category',
        'type' => CategoryType::Expense,
        'color' => '#B4432F',
        'is_archived' => true,
    ]);

    $defaultResponse = $this->actingAs($user)->get('/categories');
    $defaultResponse->assertSee('Active Category');
    $defaultResponse->assertDontSee('Archived Category');

    $showResponse = $this->actingAs($user)->get('/categories?show_archived=1');
    $showResponse->assertSee('Active Category');
    $showResponse->assertSee('Archived Category');
});

test('user can delete unused category and associated budgets are cascaded', function () {
    $user = User::factory()->create();
    $category = $user->categories()->create([
        'name' => 'Temporary',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    $user->budgets()->create([
        'category_id' => $category->id,
        'month' => '2026-10-01',
        'amount_limit' => '200.00',
    ]);

    $response = $this->actingAs($user)->delete("/categories/{$category->id}");

    $response->assertRedirect('/categories');
    $response->assertSessionHas('success', 'Category deleted.');

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    $this->assertDatabaseMissing('budgets', ['category_id' => $category->id]);
});

test('cannot delete a category that has transactions or recurring rules', function () {
    $user = User::factory()->create();
    $category = $user->categories()->create([
        'name' => 'Subscriptions',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    $user->recurringTransactions()->create([
        'category_id' => $category->id,
        'amount' => '15.00',
        'frequency' => Frequency::Monthly,
        'start_date' => '2026-10-18',
    ]);

    $response = $this->actingAs($user)->delete("/categories/{$category->id}");

    $response->assertRedirect('/categories');
    $response->assertSessionHas('error', 'This category has transactions or recurring rules. Archive it instead.');

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('foreign category returns 404 for edit, update, delete, archive, restore', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $category = $user1->categories()->create([
        'name' => 'Private Category',
        'type' => CategoryType::Expense,
        'color' => '#1F5F4D',
    ]);

    $this->actingAs($user2)->get("/categories/{$category->id}/edit")->assertNotFound();

    $this->actingAs($user2)->put("/categories/{$category->id}", [
        'name' => 'Hacked Name',
        'type' => 'expense',
        'color' => '#1F5F4D',
    ])->assertNotFound();

    $this->actingAs($user2)->delete("/categories/{$category->id}")->assertNotFound();

    $this->actingAs($user2)->patch("/categories/{$category->id}/archive")->assertNotFound();

    $this->actingAs($user2)->patch("/categories/{$category->id}/restore")->assertNotFound();
});
