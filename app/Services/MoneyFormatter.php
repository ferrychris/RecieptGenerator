<?php

namespace App\Services;

class MoneyFormatter
{
    private const SYMBOLS = [
        'USD' => '$',
        'NGN' => '₦',
        'GBP' => '£',
        'EUR' => '€',
    ];

    public static function symbol(string $currency): string
    {
        return self::SYMBOLS[$currency] ?? $currency.' ';
    }

    public static function format(float|string $amount, string $currency): string
    {
        return self::symbol($currency).number_format((float) $amount, 2);
    }
}
