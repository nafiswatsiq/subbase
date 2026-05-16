<?php

namespace Nafiswatsiq\Subbase\Filament\Resources\Subscriptions\Pages;

use Nafiswatsiq\Subbase\Filament\Resources\Subscriptions\SubscriptionResource;
use Nafiswatsiq\Subbase\Models\Plan;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class EditSubscription extends EditRecord
{
    protected static string $resource = SubscriptionResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('subbase::subbase/subscription.edit_subscription');
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $targetPlan = Plan::query()->findOrFail($data['plan_id']);

        if ((int) $record->plan_id !== (int) $targetPlan->getKey()) {
            $record->changePlan($targetPlan);
            $record->refresh();
        }

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
