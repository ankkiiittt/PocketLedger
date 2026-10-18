<?php

declare(strict_types=1);

use App\Enums\Frequency;
use App\Services\RecurrenceCalculator;
use Carbon\Carbon;

test('daily recurrence adds n days', function () {
    $start = Carbon::parse('2026-10-01');

    expect(RecurrenceCalculator::occurrence($start, Frequency::Daily, 0)->toDateString())->toBe('2026-10-01')
        ->and(RecurrenceCalculator::occurrence($start, Frequency::Daily, 1)->toDateString())->toBe('2026-10-02')
        ->and(RecurrenceCalculator::occurrence($start, Frequency::Daily, 5)->toDateString())->toBe('2026-10-06');
});

test('weekly recurrence adds n weeks', function () {
    $start = Carbon::parse('2026-10-01');

    expect(RecurrenceCalculator::occurrence($start, Frequency::Weekly, 0)->toDateString())->toBe('2026-10-01')
        ->and(RecurrenceCalculator::occurrence($start, Frequency::Weekly, 1)->toDateString())->toBe('2026-10-08')
        ->and(RecurrenceCalculator::occurrence($start, Frequency::Weekly, 4)->toDateString())->toBe('2026-10-29');
});

test('monthly recurrence clamps to month end and restores original day', function () {
    // Jan 31
    $start = Carbon::parse('2026-01-31');

    expect(RecurrenceCalculator::occurrence($start, Frequency::Monthly, 0)->toDateString())->toBe('2026-01-31')
        // Feb 28 in non-leap year
        ->and(RecurrenceCalculator::occurrence($start, Frequency::Monthly, 1)->toDateString())->toBe('2026-02-28')
        // Mar 31 (day 31 returns!)
        ->and(RecurrenceCalculator::occurrence($start, Frequency::Monthly, 2)->toDateString())->toBe('2026-03-31')
        // Apr 30 (30 days in April)
        ->and(RecurrenceCalculator::occurrence($start, Frequency::Monthly, 3)->toDateString())->toBe('2026-04-30')
        // May 31 (day 31 returns!)
        ->and(RecurrenceCalculator::occurrence($start, Frequency::Monthly, 4)->toDateString())->toBe('2026-05-31');
});

test('yearly recurrence handles leap years and non-leap years', function () {
    // 29 Feb 2024 (leap year)
    $start = Carbon::parse('2024-02-29');

    expect(RecurrenceCalculator::occurrence($start, Frequency::Yearly, 0)->toDateString())->toBe('2024-02-29')
        // 2025: non-leap -> 28 Feb
        ->and(RecurrenceCalculator::occurrence($start, Frequency::Yearly, 1)->toDateString())->toBe('2025-02-28')
        // 2028: leap year -> 29 Feb returns
        ->and(RecurrenceCalculator::occurrence($start, Frequency::Yearly, 4)->toDateString())->toBe('2028-02-29');
});

test('nextOccurrenceOnOrAfter finds first occurrence on or after today', function () {
    $start = Carbon::parse('2026-01-01');
    $today = Carbon::parse('2026-05-15');

    // Monthly rule from Jan 01
    // Occurrences: Jan 1 (0), Feb 1 (1), Mar 1 (2), Apr 1 (3), May 1 (4), Jun 1 (5)
    // First occurrence on or after 2026-05-15 is Jun 1 (n=5)
    $result = RecurrenceCalculator::nextOccurrenceOnOrAfter($start, Frequency::Monthly, 0, $today);

    expect($result['runs_count'])->toBe(5)
        ->and($result['next_run_date']->toDateString())->toBe('2026-06-01');
});
