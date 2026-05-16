<?php

namespace Nafiswatsiq\Subbase\Filament\Resources\Subscriptions\Schemas;

use Nafiswatsiq\Subbase\Models\Subscription;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;

class SubscriptionFeaturesInfolist
{
    /**
     * Builds rows for the subscription infolist. Feature values may be numeric strings (quota),
     * `"true"` / `"false"`, or arbitrary text (shown as the limit label; remaining is not computed).
     *
     * @return array<int, array{name: string, slug: string, limit: string, used: int|string|null, remaining: int|string|null, valid_until: mixed}>
     */
    public static function rowsForSubscription(Subscription $record): array
    {
        $record->loadMissing('plan.features');

        $usagesByFeatureId = $record->usage()->with('feature')->get()->keyBy('feature_id');

        $locale = app()->getLocale();

        $rows = [];

        $features = $record->plan?->features;

        foreach (($features ? $features->sortBy('sort_order') : collect()) as $feature) {
            $valueRaw = trim((string) $feature->value);
            $lower = strtolower($valueRaw);
            $isBoolean = in_array($lower, ['true', 'false'], true);
            /** Numeric quota: string digits/decimals (e.g. "1", "100"). Not booleans. */
            $isNumericQuota = ! $isBoolean && $valueRaw !== '' && is_numeric($valueRaw);

            $usage = $usagesByFeatureId->get($feature->getKey());
            $usedCount = $record->getFeatureUsage($feature->slug);

            if ($isBoolean) {
                $limitLabel = $lower === 'true'
                    ? __('subbase::subbase/subscription.feature_limit_enabled')
                    : __('subbase::subbase/subscription.feature_limit_disabled');
            } else {
                $limitLabel = $valueRaw !== '' ? $valueRaw : __('subbase::subbase/subscription.feature_limit_empty');
            }

            // Only call remainings for numeric quotas (package subtracts usage from numeric value).
            $remaining = null;
            if ($isNumericQuota) {
                $remaining = $record->getFeatureRemainings($feature->slug);
            }

            $rows[] = [
                'name' => $feature->getTranslation('name', $locale),
                // 'slug' => $feature->slug,
                'limit' => $limitLabel,
                'used' => $isBoolean ? null : $usedCount,
                'remaining' => $remaining,
                'valid_until' => $usage?->valid_until,
            ];
        }

        return $rows;
    }

    public static function components(): array
    {
        return [
            RepeatableEntry::make('feature_usage_rows')
                ->hiddenLabel()
                ->constantState(fn (Subscription $record): array => self::rowsForSubscription($record))
                ->placeholder(__('subbase::subbase/subscription.no_plan_features'))
                ->table([
                    TableColumn::make(__('subbase::subbase/subscription.feature_name')),
                    TableColumn::make(__('subbase::subbase/subscription.feature_limit')),
                    TableColumn::make(__('subbase::subbase/subscription.feature_used')),
                    TableColumn::make(__('subbase::subbase/subscription.feature_remaining')),
                    TableColumn::make(__('subbase::subbase/subscription.feature_valid_until')),
                ])
                ->schema([
                    TextEntry::make('name')->hiddenLabel(),
                    TextEntry::make('limit')->hiddenLabel(),
                    TextEntry::make('used')
                        ->hiddenLabel()
                        ->formatStateUsing(fn ($state): string => $state === null
                            ? __('subbase::subbase/subscription.feature_not_applicable')
                            : (string) $state),
                    TextEntry::make('remaining')
                        ->hiddenLabel()
                        ->formatStateUsing(fn ($state): string => $state === null
                            ? __('subbase::subbase/subscription.feature_not_applicable')
                            : (string) $state),
                    TextEntry::make('valid_until')
                        ->hiddenLabel()
                        ->placeholder('—')
                        ->dateTime('d M Y H:i', config('app.timezone')),
                ]),
        ];
    }
}
