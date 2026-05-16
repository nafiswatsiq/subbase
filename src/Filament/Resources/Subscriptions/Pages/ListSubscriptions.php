<?php

namespace Nafiswatsiq\Subbase\Filament\Resources\Subscriptions\Pages;

use Nafiswatsiq\Subbase\Filament\Resources\Subscriptions\SubscriptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('subbase::subbase/subscription.navigation_label');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('subbase::subbase/subscription.create_subscription')),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('subbase::subbase/subscription.all')),
            'active' => Tab::make(__('subbase::subbase/subscription.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->findActive()),
            'inactive' => Tab::make(__('subbase::subbase/subscription.inactive'))
                ->modifyQueryUsing(fn (Builder $query) => $query->findEndedPeriod()),
            'canceled' => Tab::make(__('subbase::subbase/subscription.canceled'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('canceled_at', '!=', null)),
        ];
    }
}
