@props([
    'subscribeRoute' => null,
])

@include(app(\Nafiswatsiq\Subbase\Support\ThemeManager::class)->resolveView('subbase', 'components.plan-list'), ['subscribeRoute' => $subscribeRoute])