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

<div class="subbase-plan-list relative overflow-hidden bg-[#f4efe6] py-16 text-black font-sans">
    <div class="absolute inset-x-0 top-0 h-3 bg-black"></div>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="mb-4 text-xs font-black uppercase tracking-[0.3em] text-pink-600">01 / {{ $t('subbase::plan.pricing.label', 'Pricing') }}</p>
            <h2 class="text-4xl font-black uppercase leading-[0.95] tracking-tight text-black sm:text-6xl">
                {{ $t('subbase::plan.pricing.title', 'Simple, transparent pricing') }}
            </h2>
            <p class="mx-auto mt-6 max-w-xl text-base font-bold leading-7 text-gray-700 sm:text-lg">
                {{ $t('subbase::plan.pricing.subtitle', 'Choose the plan that fits your needs. No hidden fees.') }}
            </p>
        </div>

        <div class="mt-14 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach($plans as $plan)
                @php
                    $pricing = \Nafiswatsiq\Subbase\Helpers\PlanPriceHelper::formatWithDiscounts($plan, $currency);
                    $checkoutUrl = $subscribeRoute ? route($subscribeRoute, ['plan' => $plan->slug]) : route('subbase-payment.checkout', $plan->slug);
                    $isFeatured = (bool) ($plan->featured ?? false);
                @endphp

                <div class="relative flex min-h-[32rem] flex-col justify-between border-4 border-black p-7 transition-transform hover:-translate-y-2 {{ $isFeatured ? 'bg-[#ffd447] shadow-[10px_10px_0px_0px_rgba(0,0,0,1)]' : 'bg-white shadow-[7px_7px_0px_0px_rgba(0,0,0,1)]' }}">
                    @if($isFeatured)
                        <div class="absolute -top-5 right-4 border-2 border-black bg-pink-400 px-3 py-1 text-xs font-black uppercase tracking-wider shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                            {{ $t('subbase::plan.pricing.most_popular', 'Most Popular') }}
                        </div>
                    @endif

                    <div>
                        <div class="flex items-start justify-between gap-4 border-b-4 border-black pb-5">
                            <div>
                                <p class="mb-2 text-[10px] font-black uppercase tracking-[0.24em] text-pink-600">Plan / {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                                <h3 class="text-2xl font-black uppercase tracking-wide text-black">{{ $plan->name }}</h3>
                            </div>
                            <span class="text-2xl font-black">↗</span>
                        </div>
                        <p class="mt-5 min-h-12 text-sm font-bold leading-6 text-gray-800">{{ $plan->description }}</p>

                        <div class="mt-6 flex items-end justify-between gap-2">
                            <span class="text-4xl font-black text-black">
                                {{ $pricing['final_price'] }}
                            </span>
                            <span class="pb-1 text-xs font-black uppercase text-gray-700">/ {{ $plan->invoice_interval }}</span>
                        </div>

                        @if($pricing['discount_info'] !== null)
                            <div class="mt-2 inline-block border-2 border-black bg-pink-300 px-2 py-0.5 text-xs font-black uppercase">
                                {{ $t('subbase::plan.pricing.was', 'Was') }} {{ $pricing['original_price'] }}
                            </div>
                        @endif

                        <ul class="mt-8 space-y-3 border-t-2 border-black pt-6">
                            @foreach($plan->features as $feature)
                                <li class="flex items-center gap-3 text-sm font-bold text-black">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center border-2 border-black bg-cyan-300 font-black">✓</span>
                                    <span>{{ $feature->name }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mt-8">
                        <a href="{{ $checkoutUrl }}" class="block w-full border-4 border-black bg-black py-4 text-center font-black uppercase tracking-wider text-white transition-all hover:bg-white hover:text-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:translate-x-1 active:translate-y-1 active:shadow-none">
                            {{ $t('subbase::plan.pricing.subscribe_button', 'Get started') }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
