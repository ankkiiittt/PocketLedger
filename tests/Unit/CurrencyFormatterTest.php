<?php

declare(strict_types=1);

use App\Services\CurrencyFormatter;

test('CurrencyFormatter formats INR with Indian numbering system', function () {
    $formatted = CurrencyFormatter::format('1234567.89', 'INR');
    // Normalize non-breaking spaces
    $normalized = str_replace("\xc2\xa0", ' ', $formatted);
    $normalized = str_replace("\u{00A0}", ' ', $normalized);

    expect($normalized)->toContain('12,34,567.89');
});

test('CurrencyFormatter formats USD with western grouping', function () {
    $formatted = CurrencyFormatter::format('1234567.89', 'USD');
    $normalized = str_replace("\xc2\xa0", ' ', $formatted);
    $normalized = str_replace("\u{00A0}", ' ', $normalized);

    expect($normalized)->toContain('1,234,567.89');
});

test('CurrencyFormatter formats zero and decimal strings', function () {
    $formatted = CurrencyFormatter::format('0.00', 'INR');
    expect($formatted)->toContain('0.00');
});
