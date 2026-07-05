<?php

declare(strict_types=1);

namespace Nafiswatsiq\Subbase\Filament\Resources\Discounts\Tables;

use Nafiswatsiq\Subbase\Models\Discount;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DiscountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('subbase::subbase/discount.name'))
                    ->searchable()
                    ->description(fn (Discount $record) => $record->description),
                TextColumn::make('code')
                    ->label(__('subbase::subbase/discount.code'))
                    ->copyable()
                    ->badge()
                    ->searchable(),
                TextColumn::make('value')
                    ->label(__('subbase::subbase/discount.value'))
                    ->formatStateUsing(fn (Discount $record) => $record->getFormattedValue())
                    ->description(fn (Discount $record) => match ($record->type) {
                        'percentage' => __('subbase::subbase/discount.type_percentage'),
                        'fixed' => __('subbase::subbase/discount.type_fixed'),
                        default => $record->type,
                    }),
                TextColumn::make('used_count')
                    ->label(__('subbase::subbase/discount.used_count'))
                    ->formatStateUsing(fn (Discount $record) => $record->max_uses
                        ? "{$record->used_count} / {$record->max_uses}"
                        : "{$record->used_count} / ∞"),
                TextColumn::make('expires_at')
                    ->label(__('subbase::subbase/discount.expires_at'))
                    ->date()
                    ->placeholder('—')
                    ->color(fn (Discount $record) => $record->expires_at && $record->expires_at->isPast() ? 'danger' : null),
                IconColumn::make('is_active')
                    ->label(__('subbase::subbase/discount.is_active'))
                    ->boolean(),
            ])
            ->reorderable('priority')
            ->defaultSort('priority')
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('type')
                    ->label(__('subbase::subbase/discount.type'))
                    ->options([
                        'percentage' => __('subbase::subbase/discount.type_percentage'),
                        'fixed' => __('subbase::subbase/discount.type_fixed'),
                    ]),
                SelectFilter::make('is_active')
                    ->label(__('subbase::subbase/discount.is_active'))
                    ->options([
                        1 => __('subbase::subbase/subscription.active'),
                        0 => __('subbase::subbase/subscription.inactive'),
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                    RestoreAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}