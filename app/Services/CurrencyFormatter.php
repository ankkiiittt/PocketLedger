<?php

declare(strict_types=1);

namespace App\Services;

use NumberFormatter;

class CurrencyFormatter
{
    /**
     * Format a money amount string using the user's currency.
     * Respects Indian grouping (12,34,567.00) for INR.
     */
    public static function format(string|int|float $amount, string $currency = 'INR'): string
    {
        $currency = strtoupper($currency);
        $floatAmount = (float) $amount;
        $locale = ($currency === 'INR') ? 'en_IN' : 'en_US';

        if (class_exists(NumberFormatter::class)) {
            $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
            $formatted = $formatter->formatCurrency($floatAmount, $currency);

            if ($formatted !== false) {
                return $formatted;
            }
        }

        return $currency.' '.number_format($floatAmount, 2);
    }
}
