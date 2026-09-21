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

<div class="subbase-plan-list relative overflow-hidden bg-[#0b1020] py-20 text-white font-sans">
    <div class="absolute -top-40 -left-40 h-96 w-96 rounded-full bg-indigo-600/30 blur-3xl"></div>
    <div class="absolute -bottom-40 -right-40 h-96 w-96 rounded-full bg-purple-600/30 blur-3xl"></div>

    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="mb-4 text-xs font-semibold uppercase tracking-[0.3em] text-indigo-300">{{ $t('subbase::plan.pricing.label', 'Pricing') }} / 2025</p>
            <h2 class="text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-6xl">
                {{ $t('subbase::plan.pricing.title', 'Simple, transparent pricing') }}
            </h2>
            <p class="mx-auto mt-5 max-w-xl text-base leading-7 text-slate-300 sm:text-lg">
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

                <div class="relative flex min-h-[32rem] flex-col justify-between rounded-[2rem] border border-white/20 bg-white/[0.08] p-7 backdrop-blur-xl shadow-2xl transition-all hover:-translate-y-2 hover:bg-white/[0.13] {{ $isFeatured ? 'ring-2 ring-indigo-400 bg-white/[0.14]' : '' }}">
                    @if($isFeatured)
                        <div class="absolute -top-4 right-6 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 px-4 py-1 text-xs font-semibold text-white shadow-lg">
                            {{ $t('subbase::plan.pricing.most_popular', 'Most Popular') }}
                        </div>
                    @endif

                    <div>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="mb-3 text-[10px] uppercase tracking-[0.25em] text-indigo-300">Plan {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                                <h3 class="text-2xl font-bold text-white">{{ $plan->name }}</h3>
                            </div>
                            <span class="grid h-10 w-10 place-items-center rounded-full border border-white/20 bg-white/10 text-indigo-200">↗</span>
                        </div>
                        <p class="mt-4 min-h-12 text-sm leading-6 text-slate-300">{{ $plan->description }}</p>

                        <div class="mt-7 flex items-end justify-between gap-2 border-y border-white/10 py-5">
                            <span class="text-4xl font-extrabold text-white">
                                {{ $pricing['final_price'] }}
                            </span>
                            <span class="text-sm text-slate-400">/ {{ $plan->invoice_interval }}</span>
                        </div>

                        <ul class="mt-7 space-y-3">
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
