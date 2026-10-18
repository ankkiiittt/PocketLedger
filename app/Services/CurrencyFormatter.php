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

    /**
     * Format a money amount without currency symbol/code, using appropriate grouping.
     */
    public static function formatNumber(string|int|float $amount, string $currency = 'INR'): string
    {
        $currency = strtoupper($currency);
        $floatAmount = (float) $amount;
        $locale = ($currency === 'INR') ? 'en_IN' : 'en_US';

        if (class_exists(NumberFormatter::class)) {
            $formatter = new NumberFormatter($locale, NumberFormatter::DECIMAL);
            $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 2);
            $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 2);
            $formatted = $formatter->format($floatAmount);

            if ($formatted !== false) {
                return $formatted;
            }
        }

        return number_format($floatAmount, 2);
    }
}
