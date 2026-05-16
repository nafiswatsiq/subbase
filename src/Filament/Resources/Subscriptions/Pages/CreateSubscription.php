<?php

namespace Nafiswatsiq\Subbase\Filament\Resources\Subscriptions\Pages;

use Nafiswatsiq\Subbase\Filament\Resources\Subscriptions\SubscriptionResource;
use Nafiswatsiq\Subbase\Models\Plan;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('subbase::subbase/subscription.create_subscription');
    }

    protected function handleRecordCreation(array $data): Model
    {
        $userClass = config('subbase.models.user');
        $user = app($userClass)->findOrFail($data['subscriber_id']);
        $plan = Plan::query()->findOrFail($data['plan_id']);
        $name = $user->name;

        return $user->newPlanSubscription($name, $plan);
    }
}
