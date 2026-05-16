<?php

namespace Nafiswatsiq\Subbase\Filament\Resources\Plans\RelationManagers;

use Nafiswatsiq\Subbase\Models\Feature;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Laravelcm\Subscriptions\Interval;

class FeaturesRelationManager extends RelationManager
{
    protected static string $relationship = 'features';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Repeater::make('translations')
                    ->label(__('subbase::subbase/plan.feature_translations'))
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
                            ->options(Feature::localeLanguageMap())
                            ->default(fn () => Feature::defaultFormLanguage())
                            ->searchable()
                            ->required(),
                        TextInput::make('name')
                            ->label(__('subbase::subbase/plan.name'))
                            ->placeholder('e.g., Api Documentation')
                            ->required(),
                        Textarea::make('description')
                            ->label(__('subbase::subbase/plan.description'))
                            ->placeholder('e.g., Api Documentation')
                            ->rows(1)
                            ->required(),
                    ])
                    ->defaultItems(1)
                    ->addActionLabel(__('subbase::subbase/plan.add_feature_translation'))
                    ->reorderable(false)
                    ->columnSpanFull()
                    ->minItems(1),
                TextInput::make('value')
                    ->label(__('subbase::subbase/plan.feature_value'))
                    ->placeholder('e.g., 100, 200, etc.')
                    ->hint('')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('resettable_period')
                    ->label(__('subbase::subbase/plan.resettable_period'))
                    ->numeric()
                    ->required()
                    ->placeholder('e.g., 1 for every month'),
                Select::make('resettable_interval')
                    ->label(__('subbase::subbase/plan.resettable_interval'))
                    ->default(Interval::MONTH->value)
                    ->options([
                        Interval::DAY->value => 'Daily',
                        Interval::MONTH->value => 'Monthly',
                        Interval::YEAR->value => 'Yearly',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('subbase::subbase/plan.name'))
                    ->searchable(),
                TextColumn::make('value')
                    ->label(__('subbase::subbase/plan.feature_value')),
                TextColumn::make('resettable_period')
                    ->label(__('subbase::subbase/plan.resettable_period')),
                TextColumn::make('resettable_interval')
                    ->label(__('subbase::subbase/plan.resettable_interval')),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(fn (array $data): array => $this->transformFeatureTranslationsForSave($data)),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateRecordDataUsing(function (array $data, Feature $record): array {
                        unset($data['name'], $data['description']);

                        $rows = Feature::repeaterRowsFromTranslations(
                            $record->getTranslations('name'),
                            $record->getTranslations('description'),
                        );

                        $data['translations'] = $rows !== [] ? $rows : [
                            [
                                'language' => Feature::defaultFormLanguage(),
                                'name' => '',
                                'description' => '',
                            ],
                        ];

                        return $data;
                    })
                    ->mutateDataUsing(fn (array $data): array => $this->transformFeatureTranslationsForSave($data)),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function transformFeatureTranslationsForSave(array $data): array
    {
        if (! isset($data['translations']) || ! is_array($data['translations'])) {
            return $data;
        }

        $parsed = Feature::translationsFromRepeaterRows($data['translations']);
        $data['name'] = $parsed['name'];
        $data['description'] = $parsed['description'];
        unset($data['translations']);

        return $data;
    }
}
