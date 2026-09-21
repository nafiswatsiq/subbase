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

<div class="subbase-plan-list bg-black py-16 text-yellow-400 font-mono">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-4xl font-black uppercase tracking-widest text-yellow-400 drop-shadow-[0_0_10px_rgba(250,204,21,0.8)] sm:text-5xl">
                // {{ $t('subbase::plan.pricing.title', 'Simple, transparent pricing') }} \\
            </h2>
            <p class="mt-4 text-sm font-bold uppercase tracking-wider text-cyan-400">
                [ {{ $t('subbase::plan.pricing.subtitle', 'Choose the plan that fits your needs. No hidden fees.') }} ]
            </p>
        </div>

        <div class="mt-14 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach($plans as $plan)
                @php
                    $pricing = \Nafiswatsiq\Subbase\Helpers\PlanPriceHelper::formatWithDiscounts($plan, $currency);
                    $checkoutUrl = route('subbase-payment.checkout', $plan->slug);
                    $isFeatured = (bool) ($plan->featured ?? false);
                @endphp

                <div class="relative flex flex-col justify-between border-2 border-cyan-400 bg-slate-950 p-8 shadow-[0_0_20px_rgba(6,182,212,0.3)] transition-all hover:border-yellow-400 hover:shadow-[0_0_30px_rgba(250,204,21,0.5)] {{ $isFeatured ? 'ring-2 ring-fuchsia-500' : '' }}">
                    @if($isFeatured)
                        <div class="absolute -top-3.5 right-4 bg-fuchsia-600 px-3 py-0.5 text-xs font-black uppercase text-white shadow-[0_0_10px_rgba(217,70,239,0.8)]">
                            ⚡ {{ $t('subbase::plan.pricing.most_popular', 'Most Popular') }}
                        </div>
                    @endif

                    <div>
                        <div class="text-xs text-cyan-400">[SYSTEM.PLAN_ID: {{ $plan->slug }}]</div>
                        <h3 class="mt-2 text-2xl font-black uppercase text-yellow-400 tracking-wider">{{ $plan->name }}</h3>
                        <p class="mt-2 text-xs font-medium text-slate-400">{{ $plan->description }}</p>

                        <div class="mt-6 flex items-baseline gap-2 border-b border-cyan-400/30 pb-4">
                            <span class="text-4xl font-black text-white drop-shadow-[0_0_8px_rgba(255,255,255,0.6)]">
                                {{ $pricing['final_price'] }}
                            </span>
                            <span class="text-xs uppercase text-cyan-400">/ {{ $plan->invoice_interval }}</span>
                        </div>

                        <ul class="mt-6 space-y-2.5">
                            @foreach($plan->features as $feature)
                                <li class="flex items-center gap-2 text-xs text-cyan-300">
                                    <span class="text-yellow-400">&gt;&gt;</span>
                                    <span>{{ $feature->name }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mt-8">
                        <a href="{{ $checkoutUrl }}" class="block w-full border-2 border-yellow-400 bg-yellow-400 py-3.5 text-center font-black uppercase tracking-widest text-black shadow-[0_0_15px_rgba(250,204,21,0.6)] transition-all hover:bg-cyan-400 hover:border-cyan-400 hover:shadow-[0_0_20px_rgba(6,182,212,0.8)]">
                            INITIALIZE_SUB //
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
