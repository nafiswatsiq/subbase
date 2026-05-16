<?php

namespace Nafiswatsiq\Subbase\Filament\Resources\Plans\Pages;

use Nafiswatsiq\Subbase\Filament\Resources\Plans\PlanResource;
use Nafiswatsiq\Subbase\Models\Plan;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreatePlan extends CreateRecord
{
    protected static string $resource = PlanResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('subbase::subbase/plan.create_plan');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $slug = Plan::slugFromFormData($data);
        if ($slug !== '') {
            $data['slug'] = $slug;
        }

        if (isset($data['translations'])) {
            $parsed = Plan::translationsFromRepeaterRows($data['translations']);
            $data['name'] = $parsed['name'];
            $data['description'] = $parsed['description'];
            unset($data['translations']);
        }

        if (isset($data['prices']) && Plan::formPricesLookLikeRepeaterRows($data['prices'])) {
            $parsed = Plan::pricesFromRepeaterRows($data['prices']);
            unset($data['prices']);
            $data['currency'] = $parsed['currency'];
            $data['prices'] = $parsed['prices'];
        }

        return $data;
    }
}
