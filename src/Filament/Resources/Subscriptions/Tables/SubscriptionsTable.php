<?php

namespace Nafiswatsiq\Subbase\Filament\Resources\Subscriptions\Tables;

use Nafiswatsiq\Subbase\Filament\Resources\Subscriptions\Schemas\SubscriptionFeaturesInfolist;
use Nafiswatsiq\Subbase\Models\Plan;
use Nafiswatsiq\Subbase\Models\Subscription;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subscriber.name')
                    ->label(__('subbase::subbase/subscription.subscriber'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('plan.name')
                    ->label(__('subbase::subbase/subscription.plan'))
                    ->formatStateUsing(fn ($state, $record): string => $record->plan?->getTranslation('name', app()->getLocale()).' '.$record->plan->invoice_period.' '.$record->plan->invoice_interval)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('subbase::subbase/subscription.subscription_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('subbase::subbase/subscription.slug'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('trial_ends_at')
                    ->label(__('subbase::subbase/subscription.trial_ends_at'))
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label(__('subbase::subbase/subscription.starts_at'))
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label(__('subbase::subbase/subscription.ends_at'))
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('canceled_at')
                    ->label(__('subbase::subbase/subscription.canceled_at'))
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('subbase::subbase/subscription.created_at'))
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('plan')
                    ->label(__('subbase::subbase/plan.navigation_label'))
                    ->options(fn (): array => Plan::query()
                        ->get()
                        ->mapWithKeys(fn (Plan $plan): array => [$plan->id => $plan->getTranslation('name', app()->getLocale()).' '.$plan->invoice_period.' '.$plan->invoice_interval])
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => $data['value'] != null ? $query->byPlanId($data['value']) : $query),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->modalHeading(__('subbase::subbase/subscription.view_features_modal_heading'))
                        ->modalWidth(Width::FiveExtraLarge)
                        ->schema(SubscriptionFeaturesInfolist::components()),
                    Action::make('renew')
                        ->label(__('subbase::subbase/subscription.renew'))
                        ->icon('heroicon-m-arrow-path')
                        ->color(Color::Blue)
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-o-question-mark-circle')
                        ->action(fn (Subscription $record) => $record->renew()),
                    Action::make('reset')
                        ->label(__('subbase::subbase/subscription.reset'))
                        ->icon('heroicon-m-arrow-path')
                        ->color(Color::Green)
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-o-question-mark-circle')
                        ->action(fn (Subscription $record) => $record->usage()->delete())
                        ->hidden(fn (Subscription $record) => $record->canceled_at != null),
                    Action::make('cancel')
                        ->label(__('subbase::subbase/subscription.cancel'))
                        ->icon('heroicon-m-x-mark')
                        ->color(Color::Red)
                        ->requiresConfirmation()
                        ->action(fn (Subscription $record) => $record->cancel())
                        ->hidden(fn (Subscription $record) => $record->canceled_at != null),
                    EditAction::make(),
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', direction: 'desc');
    }
}
