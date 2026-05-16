<?php

declare(strict_types=1);

namespace Nafiswatsiq\Subbase\Filament\Resources\Plans;

use Nafiswatsiq\Subbase\Filament\Resources\Plans\Pages\CreatePlan;
use Nafiswatsiq\Subbase\Filament\Resources\Plans\Pages\EditPlan;
use Nafiswatsiq\Subbase\Filament\Resources\Plans\Pages\ListPlans;
use Nafiswatsiq\Subbase\Filament\Resources\Plans\RelationManagers\FeaturesRelationManager;
use Nafiswatsiq\Subbase\Filament\Resources\Plans\Schemas\PlanForm;
use Nafiswatsiq\Subbase\Filament\Resources\Plans\Tables\PlansTable;
use Nafiswatsiq\Subbase\Models\Plan;
use Nafiswatsiq\Subbase\Support\SubbasePermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.plan'));
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canCreate(): bool
    {
        return static::canAccess();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canAccess();
    }

    public static function canDelete(Model $record): bool
    {
        return static::canAccess();
    }

    public static function canDeleteAny(): bool
    {
        return static::canAccess();
    }

    public static function canForceDelete(Model $record): bool
    {
        return static::canAccess();
    }

    public static function canForceDeleteAny(): bool
    {
        return static::canAccess();
    }

    public static function canRestore(Model $record): bool
    {
        return static::canAccess();
    }

    public static function canRestoreAny(): bool
    {
        return static::canAccess();
    }

    public static function canReplicate(Model $record): bool
    {
        return static::canAccess();
    }

    public static function canView(Model $record): bool
    {
        return static::canAccess();
    }

    public static function getNavigationLabel(): string
    {
        return __('subbase::subbase/plan.navigation_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('subbase::subbase/plan.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return PlanForm::configure($schema);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function table(Table $table): Table
    {
        return PlansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return SubbasePermission::allows(config('subbase.permissions.feature'))
            ? [FeaturesRelationManager::class]
            : [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlans::route('/'),
            'create' => CreatePlan::route('/create'),
            'edit' => EditPlan::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}