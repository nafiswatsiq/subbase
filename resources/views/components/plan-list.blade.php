@props([
    'subscribeRoute' => null,
    'label' => null,
    'title' => null,
    'subtitle' => null,
])

@include(app(\Nafiswatsiq\Subbase\Support\ThemeManager::class)->resolveView('subbase', 'components.plan-list'), [
    'subscribeRoute' => $subscribeRoute,
    'label' => $label,
    'title' => $title,
    'subtitle' => $subtitle,
])