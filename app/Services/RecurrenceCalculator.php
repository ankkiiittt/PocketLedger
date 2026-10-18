<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Frequency;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class RecurrenceCalculator
{
    /**
     * Compute the n-th occurrence date from start date (n = 0 is start date).
     */
    public static function occurrence(CarbonInterface|string $start, Frequency|string $frequency, int $n): Carbon
    {
        $startDate = is_string($start) ? Carbon::parse($start) : Carbon::parse($start->format('Y-m-d'));
        $freq = is_string($frequency) ? Frequency::from($frequency) : $frequency;

        if ($n <= 0) {
            return $startDate->startOfDay();
        }

        $date = match ($freq) {
            Frequency::Daily => $startDate->copy()->addDays($n),
            Frequency::Weekly => $startDate->copy()->addWeeks($n),
            Frequency::Monthly => $startDate->copy()->addMonthsNoOverflow($n),
            Frequency::Yearly => $startDate->copy()->addYearsNoOverflow($n),
        };

        return Carbon::parse($date->format('Y-m-d'))->startOfDay();
    }

    /**
     * Compute the next occurrence on or after today for resuming after pause.
     *
     * @return array{runs_count: int, next_run_date: Carbon}
     */
    public static function nextOccurrenceOnOrAfter(
        CarbonInterface|string $start,
        Frequency|string $frequency,
        int $startingN = 0,
        ?CarbonInterface $today = null
    ): array {
        $today = $today ? $today->copy()->startOfDay() : Carbon::today();
        $n = max($startingN, 0);

        while (true) {
            $date = self::occurrence($start, $frequency, $n);
            if ($date->greaterThanOrEqualTo($today)) {
                return [
                    'runs_count' => $n,
                    'next_run_date' => $date,
                ];
            }
            $n++;
            if ($n > 10000) {
                break;
            }
        }

        return [
            'runs_count' => $n,
            'next_run_date' => $date,
        ];
    }
}
