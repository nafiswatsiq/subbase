<?php

namespace Nafiswatsiq\Subbase\Filament\Resources\Plans\Schemas;

use Nafiswatsiq\Subbase\Models\Plan;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Laravelcm\Subscriptions\Interval;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('subbase::subbase/plan.basic_information'))
                    ->icon('heroicon-o-information-circle')
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        Repeater::make('translations')
                            ->label(__('subbase::subbase/plan.plan_translations'))
                            ->table([
                                TableColumn::make(__('subbase::subbase/plan.language'))
                                    ->markAsRequired(),
                                TableColumn::make(__('subbase::subbase/plan.name'))
                                    ->markAsRequired(),
                                TableColumn::make(__('subbase::subbase/plan.description'))
                                    ->markAsRequired(),
                            ])
                            ->schema([
                                Select::make('language')
                                    ->label(__('subbase::subbase/plan.language'))
                                    ->options(Plan::localeLanguageMap())
                                    ->default(fn () => Plan::defaultFormLanguage())
                                    ->searchable()
                                    ->required(),
                                TextInput::make('name')
                                    ->label(__('subbase::subbase/plan.name'))
                                    ->placeholder('e.g., Premium, Basic, etc.')
                                    ->required(),
                                Textarea::make('description')
                                    ->label(__('subbase::subbase/plan.description'))
                                    ->placeholder('e.g., This is a premium plan, etc.')
                                    ->rows(1)
                                    ->required(),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel(__('subbase::subbase/plan.add_translation'))
                            ->reorderable(false)
                            ->minItems(1),
                    ]),
                Section::make(__('subbase::subbase/plan.pricing_details'))
                    ->icon('heroicon-o-currency-dollar')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('prices')
                            ->label(__('subbase::subbase/plan.prices'))
                            ->table([
                                TableColumn::make(__('subbase::subbase/plan.currency'))
                                    ->markAsRequired(),
                                TableColumn::make(__('subbase::subbase/plan.price'))
                                    ->markAsRequired(),
                                TableColumn::make(__('subbase::subbase/plan.is_default'))
                                    ->width('80px')
                                    ->markAsRequired(),
                            ])
                            ->schema([
                                Select::make('currency')
                                    ->label(__('subbase::subbase/plan.currency'))
                                    ->options(Plan::currencySelectOptions())
                                    ->default(fn () => Plan::currencyFromLocale())
                                    ->searchable()
                                    ->required(),
                                TextInput::make('price')
                                    ->label(__('subbase::subbase/plan.price'))
                                    ->required()
                                    ->numeric()
                                    ->placeholder('e.g., 10000, 29.99, etc.'),
                                Toggle::make('is_default')
                                    ->label(__('subbase::subbase/plan.is_default'))
                                    ->inline(false)
                                    ->default(false)
                                    ->required(),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel(__('subbase::subbase/plan.add_price'))
                            ->reorderable(false)
                            ->minItems(1),
                    ]),
                Section::make(__('subbase::subbase/plan.period_details'))
                    ->icon('heroicon-o-clock')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('invoice_interval')
                            ->label(__('subbase::subbase/plan.billing_interval'))
                            ->required()
                            ->options([
                                Interval::DAY->value => 'Daily',
                                Interval::MONTH->value => 'Monthly',
                                Interval::YEAR->value => 'Yearly',
                            ]),
                        TextInput::make('invoice_period')
                            ->label(__('subbase::subbase/plan.billing_period'))
                            ->required()
                            ->numeric()
                            ->placeholder('e.g., 1 for every month'),
                        Select::make('trial_interval')
                            ->label(__('subbase::subbase/plan.trial_interval'))
                            ->default(Interval::DAY->value)
                            ->options([
                                Interval::DAY->value => 'Daily',
                                Interval::MONTH->value => 'Monthly',
                                Interval::YEAR->value => 'Yearly',
                            ])
                            ->required(),
                        TextInput::make('trial_period')
                            ->label(__('subbase::subbase/plan.trial_period'))
                            ->default(0)
                            ->numeric()
                            ->placeholder('e.g., 7 for 7 days'),
                        Select::make('grace_interval')
                            ->label(__('subbase::subbase/plan.grace_interval'))
                            ->default(Interval::DAY->value)
                            ->options([
                                Interval::DAY->value => 'Daily',
                                Interval::MONTH->value => 'Monthly',
                                Interval::YEAR->value => 'Yearly',
                            ])
                            ->required(),
                        TextInput::make('grace_period')
                            ->label(__('subbase::subbase/plan.grace_period'))
                            ->default(0)
                            ->numeric()
                            ->placeholder('e.g., 3 for 3 days'),
                    ]),
            ]);
    }
}
