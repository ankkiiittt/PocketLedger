<?php

declare(strict_types=1);

test('/_styleguide returns 404 outside local environment', function () {
    // In test runner, APP_ENV is testing
    expect(app()->environment('local'))->toBeFalse();

    $response = $this->get('/_styleguide');
    $response->assertStatus(404);
});
