@props([
    'plans' => null,
    'period' => 'monthly',
    'locale' => null,
    'subscribeRoute' => null,
])

@php
    if (!isset($plans) || $plans === null) {
        try {
            $plans = config('subbase.models.plan', \Nafiswatsiq\Subbase\Models\Plan::class)::active()
                ->with(['features', 'discounts'])
                ->orderBy('sort_order')
                ->get();
        } catch (\Throwable $e) {
            $plans = collect();
        }
    }
    $currentLocale = $locale ?? app()->getLocale();
    $planModel = config('subbase.models.plan', \Nafiswatsiq\Subbase\Models\Plan::class);
    $currency = $planModel::currencyFromLocale($currentLocale);
    $t = function ($key, $default) {
        $translation = __($key);

        return $translation === $key ? $default : $translation;
    };
@endphp

<div class="subbase-plan-list relative overflow-hidden bg-slate-950 py-16 text-white font-sans">
    <div class="absolute -top-40 -left-40 h-96 w-96 rounded-full bg-indigo-600/30 blur-3xl"></div>
    <div class="absolute -bottom-40 -right-40 h-96 w-96 rounded-full bg-purple-600/30 blur-3xl"></div>

    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                {{ $t('subbase::plan.pricing.title', 'Simple, transparent pricing') }}
            </h2>
            <p class="mt-4 text-lg text-slate-300">
                {{ $t('subbase::plan.pricing.subtitle', 'Choose the plan that fits your needs. No hidden fees.') }}
            </p>
        </div>

        <div class="mt-14 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach($plans as $plan)
                @php
                    $pricing = \Nafiswatsiq\Subbase\Helpers\PlanPriceHelper::formatWithDiscounts($plan, $currency);
                    $checkoutUrl = route('subbase-payment.checkout', $plan->slug);
                    $isFeatured = (bool) ($plan->featured ?? false);
                @endphp

                <div class="relative flex flex-col justify-between rounded-3xl border border-white/20 bg-white/10 p-8 backdrop-blur-xl shadow-2xl transition-transform hover:-translate-y-1.5 {{ $isFeatured ? 'ring-2 ring-indigo-400 bg-white/15' : '' }}">
                    @if($isFeatured)
                        <div class="absolute -top-4 right-6 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 px-4 py-1 text-xs font-semibold text-white shadow-lg">
                            {{ $t('subbase::plan.pricing.most_popular', 'Most Popular') }}
                        </div>
                    @endif

                    <div>
                        <h3 class="text-2xl font-bold text-white">{{ $plan->name }}</h3>
                        <p class="mt-2 text-sm text-slate-300">{{ $plan->description }}</p>

                        <div class="mt-6 flex items-baseline gap-2">
                            <span class="text-4xl font-extrabold text-white">
                                {{ $pricing['final_price'] }}
                            </span>
                            <span class="text-sm text-slate-400">/ {{ $plan->invoice_interval }}</span>
                        </div>

                        <ul class="mt-8 space-y-3 border-t border-white/10 pt-6">
                            @foreach($plan->features as $feature)
                                <li class="flex items-center gap-3 text-sm text-slate-200">
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-bold">✓</span>
                                    <span>{{ $feature->name }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mt-8">
                        <a href="{{ $checkoutUrl }}" class="block w-full rounded-2xl bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 py-3.5 text-center font-semibold text-white shadow-lg transition-opacity hover:opacity-90">
                            {{ $t('subbase::plan.pricing.subscribe_button', 'Get started') }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
