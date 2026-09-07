<?php

namespace Nafiswatsiq\Subbase\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Laravelcm\Subscriptions\Models\Subscription as BaseSubscription;
use LogicException;
use Nafiswatsiq\Subbase\Models\Discount;

class Subscription extends BaseSubscription
{
    /**
     * The discounts that have been applied to this subscription.
     */
    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class)
            ->withPivot(['discounted_amount', 'original_amount', 'currency', 'applied_at']);
    }

    /**
     * Renew subscription period.
     * Akumulasi ends_at jika langganan masih aktif.
     */
    public function renew(): self
    {
        if ($this->ended() && $this->canceled()) {
            throw new LogicException('Unable to renew canceled ended subscription.');
        }

        $subscription = $this;

        DB::transaction(function () use ($subscription): void {
            // Clear usage data
            $subscription->usage()->delete();

            // Akumulasi tanggal jika langganan belum berakhir
            $isFuture = $subscription->ends_at && $subscription->ends_at->isFuture();
            $originalStartsAt = $subscription->starts_at;
            $baseStart = $isFuture ? $subscription->ends_at->copy() : Carbon::now();

            $interval = $subscription->plan->invoice_interval ?? 'month';
            $count = (int) ($subscription->plan->invoice_period ?? 1);
            $method = 'add' . ucfirst($interval) . 's';

            $newEndsAt = $baseStart->{$method}($count);

            $subscription->fill([
                'starts_at' => $isFuture ? $originalStartsAt : Carbon::now(),
                'ends_at' => $newEndsAt,
                'canceled_at' => null,
            ]);

            $subscription->save();
        });

        return $this;
    }
}
