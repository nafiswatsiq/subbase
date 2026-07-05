<?php

declare(strict_types=1);

namespace Nafiswatsiq\Subbase\Filament\Resources\Discounts\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDiscount extends CreateRecord
{
    protected static string $resource = \Nafiswatsiq\Subbase\Filament\Resources\Discounts\DiscountResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Store applicable_plans and applicable_features as arrays
        $data['applicable_plans'] = $data['applicable_plans'] ?? [];
        $data['applicable_features'] = $data['applicable_features'] ?? [];

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        $data = $this->form->getState();

        // Sync plans to pivot table
        if (in_array('plans', (array) $data['applies_to'])) {
            $planIds = $data['applicable_plans'] ?? [];
            if (empty($planIds)) {
                // If no specific plans selected, sync all active plans
                $planIds = \Nafiswatsiq\Subbase\Models\Plan::where('is_active', true)
                    ->pluck('id')
                    ->toArray();
            }
            $record->plans()->sync($planIds);
        } else {
            $record->plans()->detach();
        }

        // Sync features to pivot table
        if (in_array('features', (array) $data['applies_to'])) {
            $featureIds = $data['applicable_features'] ?? [];
            if (empty($featureIds)) {
                // If no specific features selected, sync all features
                $featureIds = \Nafiswatsiq\Subbase\Models\Feature::pluck('id')->toArray();
            }
            $record->features()->sync($featureIds);
        } else {
            $record->features()->detach();
        }
    }
}