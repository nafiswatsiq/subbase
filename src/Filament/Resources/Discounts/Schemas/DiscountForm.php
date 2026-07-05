<?php

declare(strict_types=1);

namespace Nafiswatsiq\Subbase\Filament\Resources\Discounts\Schemas;

use Nafiswatsiq\Subbase\Models\Feature;
use Nafiswatsiq\Subbase\Models\Plan;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DiscountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ── Basic Information ──────────────────────────────────────
                Section::make(__('subbase::subbase/discount.basic_information'))
                    ->icon('heroicon-o-information-circle')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('subbase::subbase/discount.name'))
                            ->placeholder('e.g., Early Bird Discount')
                            ->required()
                            ->columnSpan(1),
                        TextInput::make('code')
                            ->label(__('subbase::subbase/discount.code'))
                            ->placeholder('e.g., EARLYBIRD20')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->columnSpan(1),
                        Textarea::make('description')
                            ->label(__('subbase::subbase/discount.description'))
                            ->placeholder('e.g., This discount is for early adopters...')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                // ── Discount Value ─────────────────────────────────────────
                Section::make(__('subbase::subbase/discount.discount_value'))
                    ->icon('heroicon-o-tag')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Select::make('type')
                            ->label(__('subbase::subbase/discount.type'))
                            ->options([
                                'percentage' => __('subbase::subbase/discount.type_percentage'),
                                'fixed' => __('subbase::subbase/discount.type_fixed'),
                            ])
                            ->required()
                            ->default('percentage')
                            ->live()
                            ->columnSpan(1),
                        TextInput::make('value')
                            ->label(__('subbase::subbase/discount.value'))
                            ->placeholder(fn (callable $get) => $get('type') === 'percentage' ? 'e.g., 10' : 'e.g., 50000')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->suffix(fn (callable $get) => $get('type') === 'percentage' ? '%' : null)
                            ->columnSpan(1),
                        Select::make('currency')
                            ->label(__('subbase::subbase/discount.currency'))
                            ->options(Plan::currencySelectOptions())
                            ->default(fn () => Plan::currencyFromLocale())
                            ->searchable()
                            ->visible(fn (callable $get) => $get('type') === 'fixed')
                            ->columnSpan(1),
                        TextInput::make('min_amount')
                            ->label(__('subbase::subbase/discount.min_amount'))
                            ->placeholder('e.g., 10000')
                            ->numeric()
                            ->minValue(0)
                            ->helperText(__('subbase::subbase/discount.min_amount_hint'))
                            ->columnSpan(fn (callable $get) => $get('type') === 'fixed' ? 3 : 2),
                    ]),

                // ── Usage & Schedule ───────────────────────────────────────
                Section::make(__('subbase::subbase/discount.usage_limits'))
                    ->icon('heroicon-o-bolt')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Grid::make(2)
                            ->columnSpanFull()
                            ->schema([
                                Toggle::make('is_active')
                                    ->label(__('subbase::subbase/discount.is_active'))
                                    ->default(true)
                                    ->inline(false)
                                    ->columnSpan(1),
                                TextInput::make('priority')
                                    ->label(__('subbase::subbase/discount.priority'))
                                    ->helperText(__('subbase::subbase/discount.priority_hint'))
                                    ->default(0)
                                    ->numeric()
                                    ->minValue(0)
                                    ->columnSpan(1),
                            ]),
                        TextInput::make('max_uses')
                            ->label(__('subbase::subbase/discount.max_uses'))
                            ->placeholder('e.g., 100 (leave empty for unlimited)')
                            ->numeric()
                            ->minValue(0)
                            ->columnSpan(1),
                        TextInput::make('used_count')
                            ->label(__('subbase::subbase/discount.used_count'))
                            ->default(0)
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpan(1),
                        DatePicker::make('starts_at')
                            ->label(__('subbase::subbase/discount.starts_at'))
                            ->native(false)
                            ->columnSpan(1),
                        DatePicker::make('expires_at')
                            ->label(__('subbase::subbase/discount.expires_at'))
                            ->native(false)
                            ->columnSpan(1),
                    ]),

                // ── Applicability ──────────────────────────────────────────
                Section::make(__('subbase::subbase/discount.applicability'))
                    ->icon('heroicon-o-link')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('applies_to')
                            ->label(__('subbase::subbase/discount.applies_to'))
                            ->options([
                                'plans' => __('subbase::subbase/discount.applies_to_plans'),
                                'features' => __('subbase::subbase/discount.applies_to_features'),
                            ])
                            ->multiple()
                            ->live()
                            ->required(),
                        CheckboxList::make('applicable_plans')
                            ->label(__('subbase::subbase/discount.applicable_plans'))
                            ->options(function () {
                                return Plan::query()
                                    ->where('is_active', true)
                                    ->get()
                                    ->mapWithKeys(function (Plan $plan): array {
                                        $name = is_array($plan->name)
                                            ? ($plan->name[app()->getLocale()] ?? reset($plan->name) ?? '')
                                            : (string) $plan->name;
                                        $period = $plan->invoice_period;
                                        $interval = $plan->invoice_interval;
                                        $label = $period && $interval
                                            ? "{$name} — {$period} {$interval}"
                                            : $name;

                                        return [$plan->id => $label];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->columns(2)
                            ->columnSpanFull()
                            ->visible(fn (callable $get) => in_array('plans', (array) $get('applies_to'))),
                        CheckboxList::make('applicable_features')
                            ->label(__('subbase::subbase/discount.applicable_features'))
                            ->options(function () {
                                return Feature::query()
                                    ->with('plan')
                                    ->get()
                                    ->mapWithKeys(function (Feature $feature): array {
                                        $featureName = is_array($feature->name)
                                            ? ($feature->name[app()->getLocale()] ?? reset($feature->name) ?? '')
                                            : (string) $feature->name;
                                        $plan = $feature->plan;
                                        $planName = '';
                                        if ($plan) {
                                            $planName = is_array($plan->name)
                                                ? ($plan->name[app()->getLocale()] ?? reset($plan->name) ?? '')
                                                : (string) $plan->name;
                                            $period = $plan->invoice_period;
                                            $interval = $plan->invoice_interval;
                                            $planName = $period && $interval
                                                ? "{$planName} {$period} {$interval}"
                                                : $planName;
                                        }
                                        $label = $planName ? "{$featureName} ({$planName})" : $featureName;

                                        return [$feature->id => $label];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->columns(2)
                            ->columnSpanFull()
                            ->visible(fn (callable $get) => in_array('features', (array) $get('applies_to'))),
                    ]),
            ]);
    }
}