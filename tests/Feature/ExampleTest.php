<?php

declare(strict_types=1);
use App\Models\User;

test('root redirects guests to login', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});

test('root redirects authenticated users to dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/');

    $response->assertRedirect('/dashboard');
});
