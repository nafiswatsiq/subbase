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

    public static function resolveWithDiscounts(Plan $plan, ?string $currency = null, bool $fallbackToBase = true): array
    {
        $originalAmount = static::resolve($plan, $currency, $fallbackToBase);
        $resolvedCurrency = static::currency($plan, $currency);
        $bestDiscount = $plan->getBestDiscount($originalAmount, $resolvedCurrency);
        $finalAmount = $originalAmount;

        if ($bestDiscount) {
            $finalAmount = $bestDiscount->calculateDiscount($originalAmount, $resolvedCurrency);
        }

        return [
            'original_amount' => $originalAmount,
            'discount_amount' => $originalAmount - $finalAmount,
            'final_amount' => $finalAmount,
            'currency' => $resolvedCurrency,
            'best_discount' => $bestDiscount,
        ];
    }

    public static function formatWithDiscounts(Plan $plan, ?string $currency = null, int $decimals = 2): array
    {
        $resolved = static::resolveWithDiscounts($plan, $currency);
        $discount = $resolved['best_discount'];

        $result = [
            'original_price' => sprintf('%s %s', $resolved['currency'], number_format($resolved['original_amount'], $decimals, '.', ',')),
            'final_price' => sprintf('%s %s', $resolved['currency'], number_format($resolved['final_amount'], $decimals, '.', ',')),
            'discount_amount' => sprintf('%s %s', $resolved['currency'], number_format($resolved['discount_amount'], $decimals, '.', ',')),
            'discount_info' => null,
        ];

        if ($discount) {
            $result['discount_info'] = [
                'code' => $discount->code,
                'type' => $discount->type,
                'value' => $discount->value,
                'formatted_value' => $discount->getFormattedValue(),
            ];
        }

        return $result;
    }
}
