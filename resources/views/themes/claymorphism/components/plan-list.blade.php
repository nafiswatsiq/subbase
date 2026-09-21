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

<div class="subbase-plan-list bg-[#e9edf5] py-20 text-slate-800 font-sans">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="mb-4 text-xs font-bold uppercase tracking-[0.3em] text-indigo-500">{{ $t('subbase::plan.pricing.label', 'Pricing') }} / Plans</p>
            <h2 class="text-4xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-6xl">
                {{ $t('subbase::plan.pricing.title', 'Simple, transparent pricing') }}
            </h2>
            <p class="mx-auto mt-5 max-w-xl text-base leading-7 text-slate-600 sm:text-lg">
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

                <div class="relative flex min-h-[32rem] flex-col justify-between rounded-[2rem] p-7 transition-transform hover:-translate-y-2 {{ $isFeatured ? 'bg-indigo-50 shadow-[14px_14px_28px_0px_rgba(99,102,241,0.15),-14px_-14px_28px_0px_rgba(255,255,255,1)] border-2 border-indigo-200' : 'bg-slate-50 shadow-[12px_12px_24px_0px_rgba(0,0,0,0.06),-12px_-12px_24px_0px_rgba(255,255,255,0.95)] border border-white' }}">
                    @if($isFeatured)
                        <div class="absolute -top-4 right-6 rounded-full bg-indigo-500 px-4 py-1 text-xs font-bold text-white shadow-[4px_4px_8px_0px_rgba(0,0,0,0.1)]">
                            {{ $t('subbase::plan.pricing.most_popular', 'Most Popular') }}
                        </div>
                    @endif

                    <div>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="mb-3 text-[10px] font-bold uppercase tracking-[0.25em] text-indigo-500">Plan {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                                <h3 class="text-2xl font-bold text-slate-900">{{ $plan->name }}</h3>
                            </div>
                            <span class="grid h-10 w-10 place-items-center rounded-full bg-indigo-100 text-indigo-600">↗</span>
                        </div>
                        <p class="mt-4 min-h-12 text-sm leading-6 text-slate-600">{{ $plan->description }}</p>

                        <div class="mt-7 flex items-end justify-between gap-2 border-y border-slate-200 py-5">
                            <span class="text-4xl font-black text-slate-900">
                                {{ $pricing['final_price'] }}
                            </span>
                            <span class="text-sm font-medium text-slate-500">/ {{ $plan->invoice_interval }}</span>
                        </div>

                        <ul class="mt-7 space-y-3">
                            @foreach($plan->features as $feature)
                                <li class="flex items-center gap-3 text-sm text-slate-700">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 font-bold text-xs shadow-inner">✓</span>
                                    <span>{{ $feature->name }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mt-8">
                        <a href="{{ $checkoutUrl }}" class="block w-full rounded-2xl bg-indigo-600 py-3.5 text-center font-bold text-white shadow-[6px_6px_12px_0px_rgba(99,102,241,0.3),-6px_-6px_12px_0px_rgba(255,255,255,0.8)] transition-all hover:bg-indigo-500 active:shadow-inner">
                            {{ $t('subbase::plan.pricing.subscribe_button', 'Get started') }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
