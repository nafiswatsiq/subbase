<?php

namespace Nafiswatsiq\Subbase\Filament\Resources\Plans\Pages;

use Nafiswatsiq\Subbase\Filament\Resources\Plans\PlanResource;
use Nafiswatsiq\Subbase\Models\Plan;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditPlan extends EditRecord
{
    protected static string $resource = PlanResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('subbase::subbase/plan.edit_plan');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $plan = $this->getRecord();

        if (! $plan instanceof Plan) {
            return $data;
        }

        unset($data['name'], $data['description']);

        $rows = Plan::repeaterRowsFromTranslations(
            $plan->getTranslations('name'),
            $plan->getTranslations('description'),
        );

        $data['translations'] = $rows !== [] ? $rows : [
            [
                'language' => Plan::defaultFormLanguage(),
                'name' => '',
                'description' => '',
            ],
        ];

        unset($data['prices']);
        $data['prices'] = $plan->repeaterRowsForPricesRepeater();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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
