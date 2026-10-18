<?php

declare(strict_types=1);

use App\Services\MonthRange;
use Carbon\Carbon;

test('MonthRange parses valid YYYY-MM string correctly', function () {
    $range = MonthRange::fromString('2026-10');

    expect($range->currentMonth)->toBe('2026-10')
        ->and($range->label)->toBe('October 2026')
        ->and($range->prevMonth)->toBe('2026-09')
        ->and($range->nextMonth)->toBe('2026-11')
        ->and($range->startDateString())->toBe('2026-10-01')
        ->and($range->nextStartDateString())->toBe('2026-11-01');
});

test('MonthRange falls back to current month on invalid input', function () {
    Carbon::setTestNow('2026-05-15');

    $range = MonthRange::fromString('invalid-month');

    expect($range->currentMonth)->toBe('2026-05')
        ->and($range->label)->toBe('May 2026')
        ->and($range->isCurrentMonth)->toBeTrue();

    Carbon::setTestNow(); // reset
});

test('MonthRange handles year rollover properly', function () {
    $range = MonthRange::fromString('2026-01');

    expect($range->prevMonth)->toBe('2025-12')
        ->and($range->nextMonth)->toBe('2026-02');

    $rangeDec = MonthRange::fromString('2026-12');
    expect($rangeDec->nextMonth)->toBe('2027-01');
});
