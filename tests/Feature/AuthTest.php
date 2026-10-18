<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('guest visiting root URL is redirected to login', function () {
    $response = $this->get('/');
    $response->assertRedirect('/login');
});

test('authenticated user visiting root URL is redirected to dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/');
    $response->assertRedirect('/dashboard');
});

test('registration seeds default categories and signs user in', function () {
    $response = $this->post('/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'currency_code' => 'INR',
    ]);

    $response->assertRedirect('/dashboard');
    $response->assertSessionHas('success', 'Welcome to PocketLedger. Your categories are ready.');

    $user = User::where('email', 'john@example.com')->first();
    expect($user)->not->toBeNull();
    expect(Auth::id())->toBe($user->id);

    // Assert 14 default categories created (10 expense + 4 income)
    expect($user->categories()->count())->toBe(14);
    expect($user->categories()->expense()->count())->toBe(10);
    expect($user->categories()->income()->count())->toBe(4);
});

test('duplicate email registration is rejected with field error', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->post('/register', [
        'name' => 'Jane Doe',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'currency_code' => 'INR',
    ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertGuest();
});

test('user can log in with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => 'secret123',
    ]);

    $response = $this->post('/login', [
        'email' => 'user@example.com',
        'password' => 'secret123',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

test('login with invalid credentials shows single generic error message', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => 'secret123',
    ]);

    $response = $this->from('/login')->post('/login', [
        'email' => 'user@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionHasErrors([
        'email' => 'These credentials do not match our records.',
    ]);
    $this->assertGuest();
});

test('login is throttled after 5 failed attempts in a minute', function () {
    $user = User::factory()->create(['email' => 'throttle@example.com']);

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email' => 'throttle@example.com',
            'password' => 'wrong-pass',
        ]);
    }

    // 6th attempt should be throttled
    $response = $this->post('/login', [
        'email' => 'throttle@example.com',
        'password' => 'wrong-pass',
    ]);

    $response->assertSessionHasErrors('email');
    $errors = session('errors')->get('email');
    expect($errors[0])->toContain('Too many attempts');
});

test('user can log out', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/login');
    $this->assertGuest();
});

test('password reset link flow sends notification', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'reset@example.com']);

    $response = $this->post('/forgot-password', [
        'email' => 'reset@example.com',
    ]);

    $response->assertSessionHas('status', 'If that email exists, a reset link has been sent.');

    Notification::assertSentTo($user, ResetPassword::class);
});

test('guests are redirected from protected routes', function () {
    $response = $this->get('/settings');
    $response->assertRedirect('/login');

    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});
