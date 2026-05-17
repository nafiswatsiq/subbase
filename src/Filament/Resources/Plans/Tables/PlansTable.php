<?php

namespace Nafiswatsiq\Subbase\Filament\Resources\Plans\Tables;

use Nafiswatsiq\Subbase\Filament\Resources\Plans\Schemas\PlanForm;
use Nafiswatsiq\Subbase\Helpers\PlanPriceHelper;
use Nafiswatsiq\Subbase\Models\Plan;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('subbase::subbase/plan.name'))
                    ->formatStateUsing(fn (Plan $record, $state) => $record->getTranslation('name', app()->getLocale())),
                TextColumn::make('price')
                    ->label(__('subbase::subbase/plan.price'))
                    ->formatStateUsing(fn (Plan $record) => PlanPriceHelper::format($record)),
                TextColumn::make('available_currencies')
                    ->label(__('subbase::subbase/plan.available_currencies'))
                    ->getStateUsing(fn (Plan $record) => implode(', ', array_values(array_unique($record->getAvailableCurrencies()))))
                    ->wrap(),
                TextColumn::make('invoice_interval')
                    ->formatStateUsing(fn (Plan $record, $state) => $record->invoice_period.' '.ucfirst($state))
                    ->label(__('subbase::subbase/plan.billing_interval')),
                TextColumn::make('trial_interval')
                    ->formatStateUsing(fn (Plan $record, $state) => $record->trial_period.' '.ucfirst($state))
                    ->label(__('subbase::subbase/plan.trial_interval')),
                TextColumn::make('grace_interval')
                    ->formatStateUsing(fn (Plan $record, $state) => $record->grace_period.' '.ucfirst($state))
                    ->label(__('subbase::subbase/plan.grace_interval')),
                ToggleColumn::make('is_active')
                    ->label(__('subbase::subbase/plan.is_active')),
                ToggleColumn::make('featured')
                    ->label(__('subbase::subbase/plan.featured')),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ReplicateAction::make()
                        ->excludeAttributes(['slug'])
                        ->schema(function (Schema $schema): Schema {
                            return PlanForm::configure($schema);
                        })
                        ->mutateRecordDataUsing(function (array $data): array {
                            $plan = Plan::find($data['id'], ['*']);

                            if (! $plan instanceof Plan) {
                                return $data;
                            }

                            unset($data['name'], $data['description']);

                            $data['translations'] = Plan::repeaterRowsFromTranslations(
                                $plan->getTranslations('name'),
                                $plan->getTranslations('description'),
                            );

                            unset($data['prices']);
                            $data['prices'] = $plan->repeaterRowsForPricesRepeater();

                            return $data;
                        })
                        ->beforeReplicaSaved(function (array $data, Model $replica): void {
                            $slug = Plan::slugFromFormData($data);
                            if ($slug !== '') {
                                $replica->setAttribute('slug', $slug);
                            }

                            if (isset($data['translations']) && is_array($data['translations'])) {
                                $parsed = Plan::translationsFromRepeaterRows($data['translations']);
                                $replica->setAttribute('name', $parsed['name']);
                                $replica->setAttribute('description', $parsed['description']);
                            }

                            if (isset($data['prices']) && Plan::formPricesLookLikeRepeaterRows($data['prices'])) {
                                $parsed = Plan::pricesFromRepeaterRows($data['prices']);
                                $replica->setAttribute('currency', $parsed['currency']);
                                $replica->setAttribute('prices', $parsed['prices']);
                            }
                        })
                        ->after(function (Plan $record, Model $replica): void {
                            // replicate features
                            foreach ($record->features as $feature) {
                                $replica->features()->create([
                                    'slug' => $feature->slug,
                                    'name' => $feature->getTranslations('name'),
                                    'description' => $feature->getTranslations('description'),
                                    'value' => $feature->value,
                                    'resettable_period' => $feature->resettable_period,
                                    'resettable_interval' => $feature->resettable_interval,
                                ]);
                            }
                        }),
                    EditAction::make(),
                    DeleteAction::make(),
                    ForceDeleteAction::make()
                        ->before(function (Model $record): void {
                            // delete features
                            $record->features()->forceDelete();
                        }),
                    RestoreAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    // ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
