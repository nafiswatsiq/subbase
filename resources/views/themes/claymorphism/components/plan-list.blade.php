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
@endphp

<div class="subbase-plan-list bg-slate-100 py-16 text-slate-800 font-sans">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-4xl font-extrabold tracking-tight text-slate-900 sm:text-5xl">
                {{ __('subbase::subbase/frontend.plan_list.title') }}
            </h2>
            <p class="mt-4 text-lg font-medium text-slate-600">
                {{ __('subbase::subbase/frontend.plan_list.subtitle') }}
            </p>
        </div>

        <div class="mt-14 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach($plans as $plan)
                @php
                    $pricing = \Nafiswatsiq\Subbase\Helpers\PlanPriceHelper::formatWithDiscounts($plan, $currency);
                    $checkoutUrl = route('subbase-payment.checkout', $plan->slug);
                    $isFeatured = (bool) ($plan->featured ?? false);
                @endphp

                <div class="relative flex flex-col justify-between rounded-3xl p-8 transition-transform hover:-translate-y-1.5 {{ $isFeatured ? 'bg-indigo-50 shadow-[12px_12px_24px_0px_rgba(99,102,241,0.15),-12px_-12px_24px_0px_rgba(255,255,255,1)] border-2 border-indigo-200' : 'bg-slate-50 shadow-[10px_10px_20px_0px_rgba(0,0,0,0.06),-10px_-10px_20px_0px_rgba(255,255,255,0.9)] border border-white' }}">
                    @if($isFeatured)
                        <div class="absolute -top-4 right-6 rounded-full bg-indigo-500 px-4 py-1 text-xs font-bold text-white shadow-[4px_4px_8px_0px_rgba(0,0,0,0.1)]">
                            {{ __('subbase::subbase/frontend.plan_list.popular_badge') }}
                        </div>
                    @endif

                    <div>
                        <h3 class="text-2xl font-bold text-slate-900">{{ $plan->name }}</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ $plan->description }}</p>

                        <div class="mt-6 flex items-baseline gap-2">
                            <span class="text-4xl font-black text-slate-900">
                                {{ $pricing['final_price'] }}
                            </span>
                            <span class="text-sm font-medium text-slate-500">/ {{ $plan->invoice_interval }}</span>
                        </div>

                        <ul class="mt-8 space-y-3 border-t border-slate-200 pt-6">
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
                            {{ __('subbase::subbase/frontend.plan_list.subscribe_button') }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
