<?php

declare(strict_types=1);

namespace Nafiswatsiq\Subbase\Filament\Resources\Discounts\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDiscounts extends ListRecords
{
    protected static string $resource = \Nafiswatsiq\Subbase\Filament\Resources\Discounts\DiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}