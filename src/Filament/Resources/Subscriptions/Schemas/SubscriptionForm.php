<?php

namespace Nafiswatsiq\Subbase\Filament\Resources\Subscriptions\Schemas;

use Nafiswatsiq\Subbase\Models\Plan;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('subbase::subbase/subscription.basic_information'))
                    ->icon('heroicon-o-information-circle')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('subscriber_id')
                            ->label(__('subbase::subbase/subscription.subscriber'))
                            ->options(fn (): array => app(config('subbase.models.user'))->pluck('name', 'id')->all())
                            ->live()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabledOn('edit')
                            ->dehydrated(fn (string $operation): bool => $operation === 'create'),
                        Select::make('plan_id')
                            ->live()
                            ->label(__('subbase::subbase/subscription.plan'))
                            ->options(fn (): array => Plan::query()
                                ->get()
                                ->mapWithKeys(fn (Plan $plan): array => [$plan->id => $plan->getTranslation('name', app()->getLocale()).' '.$plan->invoice_period.' '.$plan->invoice_interval])
                                ->all())
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
            ]);
    }
}
