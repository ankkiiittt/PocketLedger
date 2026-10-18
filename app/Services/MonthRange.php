<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;

final class MonthRange
{
    public function __construct(
        public readonly Carbon $start,
        public readonly Carbon $nextStart,
        public readonly string $currentMonth,
        public readonly string $label,
        public readonly string $prevMonth,
        public readonly string $nextMonth,
        public readonly bool $isCurrentMonth
    ) {}

    /**
     * Create a MonthRange from a YYYY-MM string, or default to current month.
     */
    public static function fromString(?string $ym = null): self
    {
        $now = Carbon::now();
        $fallback = $now->copy()->startOfMonth();

        if ($ym === null || ! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $ym)) {
            $parsed = $fallback;
        } else {
            try {
                $parsed = Carbon::createFromFormat('Y-m-d', "{$ym}-01")->startOfMonth();
            } catch (InvalidFormatException) {
                $parsed = $fallback;
            }
        }

        $start = $parsed->copy()->startOfMonth()->startOfDay();
        $nextStart = $start->copy()->addMonthNoOverflow();
        $prevMonth = $start->copy()->subMonthNoOverflow()->format('Y-m');
        $nextMonth = $nextStart->format('Y-m');
        $currentMonth = $start->format('Y-m');
        $label = $start->format('F Y');
        $isCurrentMonth = ($currentMonth === $now->format('Y-m'));

        return new self(
            start: $start,
            nextStart: $nextStart,
            currentMonth: $currentMonth,
            label: $label,
            prevMonth: $prevMonth,
            nextMonth: $nextMonth,
            isCurrentMonth: $isCurrentMonth
        );
    }

    /**
     * First day of the month as 'Y-m-d'.
     */
    public function startDateString(): string
    {
        return $this->start->format('Y-m-d');
    }

    /**
     * First day of next month as 'Y-m-d'.
     */
    public function nextStartDateString(): string
    {
        return $this->nextStart->format('Y-m-d');
    }
}
