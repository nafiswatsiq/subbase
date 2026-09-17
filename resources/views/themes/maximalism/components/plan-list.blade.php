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

<div class="subbase-plan-list bg-purple-900 py-16 text-white font-sans overflow-hidden">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="inline-block bg-emerald-400 px-6 py-2 text-4xl font-black uppercase tracking-widest text-black rotate-1 sm:text-6xl shadow-[6px_6px_0px_0px_rgba(255,255,255,1)]">
                {{ __('subbase::subbase/frontend.plan_list.title') }}
            </h2>
            <p class="mt-6 text-2xl font-black uppercase text-pink-300">
                {{ __('subbase::subbase/frontend.plan_list.subtitle') }}
            </p>
        </div>

        <div class="mt-16 grid grid-cols-1 gap-10 md:grid-cols-2 lg:grid-cols-3">
            @foreach($plans as $plan)
                @php
                    $pricing = \Nafiswatsiq\Subbase\Helpers\PlanPriceHelper::formatWithDiscounts($plan, $currency);
                    $checkoutUrl = route('subbase-payment.checkout', $plan->slug);
                    $isFeatured = (bool) ($plan->featured ?? false);
                @endphp

                <div class="relative flex flex-col justify-between border-4 border-black p-8 transition-transform hover:scale-105 {{ $isFeatured ? 'bg-gradient-to-br from-pink-500 to-orange-400 text-black shadow-[10px_10px_0px_0px_rgba(250,204,21,1)] -rotate-1' : 'bg-white text-black shadow-[8px_8px_0px_0px_rgba(52,211,153,1)] rotate-1' }}">
                    @if($isFeatured)
                        <div class="absolute -top-5 -right-3 border-2 border-black bg-yellow-300 px-4 py-1 text-xs font-black uppercase tracking-widest text-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                            ★ {{ __('subbase::subbase/frontend.plan_list.popular_badge') }} ★
                        </div>
                    @endif

                    <div>
                        <h3 class="text-3xl font-black uppercase tracking-tight">{{ $plan->name }}</h3>
                        <p class="mt-2 text-sm font-bold opacity-90">{{ $plan->description }}</p>

                        <div class="mt-6 flex items-baseline gap-2 border-b-4 border-black pb-4">
                            <span class="text-5xl font-black">
                                {{ $pricing['formatted_final_price'] }}
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
