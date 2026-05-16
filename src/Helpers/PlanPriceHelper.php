<?php

namespace Nafiswatsiq\Subbase\Helpers;

use Nafiswatsiq\Subbase\Models\Plan;

class PlanPriceHelper
{
    public static function resolve(Plan $plan, ?string $currency = null, bool $fallbackToBase = true): float
    {
        return $plan->getPriceForCurrency($currency, $fallbackToBase);
    }

    public static function currency(Plan $plan, ?string $currency = null): string
    {
        $targetCurrency = strtoupper((string) ($currency ?: Plan::currencyFromLocale()));

        if ($targetCurrency !== '' && $plan->hasCurrencyPrice($targetCurrency)) {
            return $targetCurrency;
        }

        $available = $plan->getAvailableCurrencies();

        return $available[0] ?? strtoupper((string) $plan->currency);
    }

    public static function format(Plan $plan, ?string $currency = null, int $decimals = 2): string
    {
        $resolvedCurrency = static::currency($plan, $currency);
        $amount = static::resolve($plan, $resolvedCurrency);

        return sprintf('%s %s', $resolvedCurrency, number_format($amount, $decimals, '.', ','));
    }

    public static function all(Plan $plan): array
    {
        $result = [];

        foreach ($plan->getAvailableCurrencies() as $currency) {
            $result[$currency] = $plan->getPriceForCurrency($currency);
        }

        return $result;
    }
}
