<?php

namespace Nafiswatsiq\Subbase\Models;

use Laravelcm\Subscriptions\Models\Subscription as BaseSubscription;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
}
