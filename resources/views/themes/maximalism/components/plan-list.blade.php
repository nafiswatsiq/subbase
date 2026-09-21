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

<div class="subbase-plan-list relative overflow-hidden bg-[#34135c] py-20 text-white font-sans">
    <div class="pointer-events-none absolute -right-20 top-20 h-56 w-56 rotate-12 border-[18px] border-yellow-300/50"></div>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="relative mx-auto max-w-4xl text-center">
            <p class="mb-5 text-xs font-black uppercase tracking-[0.35em] text-yellow-300">THE SUBBASE COLLECTION / 2025</p>
            <h2 class="inline-block bg-emerald-400 px-6 py-3 text-4xl font-black uppercase leading-none tracking-widest text-black rotate-1 sm:text-6xl shadow-[7px_7px_0px_0px_rgba(255,255,255,1)]">
                {{ $t('subbase::plan.pricing.title', 'Simple, transparent pricing') }}
            </h2>
            <p class="mx-auto mt-7 max-w-2xl text-lg font-black uppercase leading-7 text-pink-300 sm:text-2xl">
                {{ $t('subbase::plan.pricing.subtitle', 'Choose the plan that fits your needs. No hidden fees.') }}
            </p>
        </div>

        <div class="relative mt-16 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach($plans as $plan)
                @php
                    $pricing = \Nafiswatsiq\Subbase\Helpers\PlanPriceHelper::formatWithDiscounts($plan, $currency);
                    $checkoutUrl = route('subbase-payment.checkout', $plan->slug);
                    $isFeatured = (bool) ($plan->featured ?? false);
                @endphp

                <div class="relative flex min-h-[32rem] flex-col justify-between border-4 border-black p-7 transition-transform hover:scale-[1.03] {{ $isFeatured ? 'bg-gradient-to-br from-pink-500 to-orange-400 text-black shadow-[10px_10px_0px_0px_rgba(250,204,21,1)] -rotate-1' : 'bg-white text-black shadow-[8px_8px_0px_0px_rgba(52,211,153,1)] rotate-1' }}">
                    @if($isFeatured)
                        <div class="absolute -top-5 -right-3 border-2 border-black bg-yellow-300 px-4 py-1 text-xs font-black uppercase tracking-widest text-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                            ★ {{ $t('subbase::plan.pricing.most_popular', 'Most Popular') }} ★
                        </div>
                    @endif

                    <div>
                        <div class="flex items-start justify-between gap-4 border-b-4 border-black pb-5">
                            <div>
                                <p class="mb-3 text-[10px] font-black uppercase tracking-[0.25em] opacity-70">Edition {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                                <h3 class="text-3xl font-black uppercase tracking-tight">{{ $plan->name }}</h3>
                            </div>
                            <span class="text-3xl font-black">↗</span>
                        </div>
                        <p class="mt-5 min-h-12 text-sm font-bold leading-6 opacity-90">{{ $plan->description }}</p>

                        <div class="mt-6 flex items-baseline gap-2 border-b-4 border-black pb-4">
                            <span class="text-5xl font-black">
                                {{ $pricing['final_price'] }}
                            </span>
                            <span class="text-xs font-black uppercase">/ {{ $plan->invoice_interval }}</span>
                        </div>

                        <ul class="mt-6 space-y-3">
                            @foreach($plan->features as $feature)
                                <li class="flex items-center gap-3 text-sm font-black">
                                    <span class="flex h-7 w-7 items-center justify-center border-2 border-black bg-yellow-300 font-black text-black">★</span>
                                    <span>{{ $feature->name }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mt-8">
                        <a href="{{ $checkoutUrl }}" class="block w-full border-4 border-black bg-black py-4 text-center text-lg font-black uppercase tracking-widest text-yellow-300 shadow-[5px_5px_0px_0px_rgba(255,255,255,1)] transition-all hover:bg-yellow-300 hover:text-black">
                            GET STARTED NOW!
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
