<?php

namespace Nafiswatsiq\Subbase\Filament\Resources\Plans\Pages;

use Nafiswatsiq\Subbase\Filament\Resources\Plans\PlanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListPlans extends ListRecords
{
    protected static string $resource = PlanResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('subbase::subbase/plan.navigation_label');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('subbase::subbase/plan.create_plan')),
        ];
    }
}
