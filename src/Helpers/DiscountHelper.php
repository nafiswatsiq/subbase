<?php

declare(strict_types=1);

namespace Nafiswatsiq\Subbase\Helpers;

use Nafiswatsiq\Subbase\Models\Discount;
use Nafiswatsiq\Subbase\Models\Plan;
use Nafiswatsiq\Subbase\Models\Subscription;

class DiscountHelper
{
    /**
     * Validate and apply a discount code to a subscription.
     */
    public static function applyDiscount(string $code, Subscription $subscription): ?array
    {
        $discount = Discount::where('code', strtoupper(trim($code)))
            ->where('is_active', true)
            ->first();

        if (! $discount) {
            return null;
        }

        if (! $discount->isValid()) {
            return null;
        }

        $plan = $subscription->plan;

        if (! $discount->appliesToPlan($plan->id)) {
            return null;
        }

        $amount = $plan->getPriceForCurrency($subscription->currency);
        $discountedAmount = $discount->calculateDiscount($amount, $subscription->currency);

        if ($discountedAmount >= $amount) {
            return null;
        }

        $discount->incrementUsage();

        $subscription->discounts()->attach($discount->id, [
            'discounted_amount' => $amount - $discountedAmount,
            'original_amount' => $amount,
            'currency' => $subscription->currency,
            'applied_at' => now(),
        ]);

        return [
            'discount' => $discount,
            'original_amount' => $amount,
            'discounted_amount' => $amount - $discountedAmount,
            'final_amount' => $discountedAmount,
            'currency' => $subscription->currency,
        ];
    }

    /**
     * Find the best applicable discount for a plan.
     */
    public static function findBestDiscount(Plan $plan, float $amount, string $currency): ?Discount
    {
        $discounts = Discount::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->orderByDesc('priority')
            ->get();

        foreach ($discounts as $discount) {
            if (! $discount->appliesToPlan($plan->id)) {
                continue;
            }

            if ($discount->calculateDiscount($amount, $currency) < $amount) {
                return $discount;
            }
        }

        return null;
    }

    /**
     * Get all active discounts for a plan.
     */
    public static function getActiveDiscountsForPlan(Plan $plan): \Illuminate\Database\Eloquent\Collection
    {
        return Discount::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->whereHas('plans', function ($query) use ($plan) {
                $query->where('plans.id', $plan->id);
            })
            ->orderByDesc('priority')
            ->get();
    }
}