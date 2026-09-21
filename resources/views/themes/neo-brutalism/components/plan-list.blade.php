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

<div class="subbase-plan-list py-12 bg-amber-50 text-black font-sans">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-4xl font-black uppercase tracking-tight text-black sm:text-5xl">
                {{ $t('subbase::plan.pricing.title', 'Simple, transparent pricing') }}
            </h2>
            <p class="mt-4 text-xl font-bold uppercase text-gray-800">
                {{ $t('subbase::plan.pricing.subtitle', 'Choose the plan that fits your needs. No hidden fees.') }}
            </p>
        </div>

        <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach($plans as $plan)
                @php
                    $pricing = \Nafiswatsiq\Subbase\Helpers\PlanPriceHelper::formatWithDiscounts($plan, $currency);
                    $checkoutUrl = route('subbase-payment.checkout', $plan->slug);
                    $isFeatured = (bool) ($plan->featured ?? false);
                @endphp

                <div class="relative flex flex-col justify-between border-4 border-black p-8 transition-transform hover:-translate-y-1 {{ $isFeatured ? 'bg-yellow-300 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]' : 'bg-white shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]' }}">
                    @if($isFeatured)
                        <div class="absolute -top-5 right-4 border-2 border-black bg-pink-400 px-3 py-1 text-xs font-black uppercase tracking-wider shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                            {{ $t('subbase::plan.pricing.most_popular', 'Most Popular') }}
                        </div>
                    @endif

                    <div>
                        <h3 class="text-2xl font-black uppercase tracking-wide text-black">{{ $plan->name }}</h3>
                        <p class="mt-2 text-sm font-bold text-gray-800">{{ $plan->description }}</p>

                        <div class="mt-6 flex items-baseline gap-2">
                            <span class="text-4xl font-black text-black">
                                {{ $pricing['final_price'] }}
                            </span>
                            <span class="text-sm font-bold uppercase text-gray-700">/ {{ $plan->invoice_interval }}</span>
                        </div>

                        @if($pricing['discount_info'] !== null)
                            <div class="mt-2 inline-block border-2 border-black bg-pink-300 px-2 py-0.5 text-xs font-black uppercase">
                                {{ $t('subbase::plan.pricing.was', 'Was') }} {{ $pricing['original_price'] }}
                            </div>
                        @endif

                        <ul class="mt-8 space-y-3 border-t-4 border-black pt-6">
                            @foreach($plan->features as $feature)
                                <li class="flex items-center gap-3 text-sm font-bold text-black">
                                    <span class="flex h-6 w-6 items-center justify-center border-2 border-black bg-cyan-300 font-black">✓</span>
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
