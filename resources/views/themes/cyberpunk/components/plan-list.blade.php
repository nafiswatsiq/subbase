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

<div class="subbase-plan-list relative overflow-hidden bg-[#05070d] py-20 text-yellow-400 font-mono">
    <div class="pointer-events-none absolute inset-0 opacity-20" style="background-image: linear-gradient(rgba(34,211,238,.25) 1px, transparent 1px), linear-gradient(90deg, rgba(34,211,238,.25) 1px, transparent 1px); background-size: 36px 36px;"></div>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="relative mx-auto max-w-3xl text-center">
            <p class="mb-4 text-xs font-bold uppercase tracking-[0.35em] text-cyan-400">[ PRICING_MODULE / ONLINE ]</p>
            <h2 class="text-4xl font-black uppercase leading-none tracking-widest text-yellow-400 drop-shadow-[0_0_10px_rgba(250,204,21,0.8)] sm:text-6xl">
                // {{ $t('subbase::plan.pricing.title', 'Simple, transparent pricing') }} \\
            </h2>
            <p class="mx-auto mt-6 max-w-xl text-sm font-bold uppercase leading-6 tracking-wider text-cyan-400">
                [ {{ $t('subbase::plan.pricing.subtitle', 'Choose the plan that fits your needs. No hidden fees.') }} ]
            </p>
        </div>

        <div class="relative mt-14 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($plans as $plan)
                @php
                    $pricing = \Nafiswatsiq\Subbase\Helpers\PlanPriceHelper::formatWithDiscounts($plan, $currency);
                    $checkoutUrl = route('subbase-payment.checkout', $plan->slug);
                    $isFeatured = (bool) ($plan->featured ?? false);
                @endphp

                <div class="relative flex min-h-[32rem] flex-col justify-between border border-cyan-400/70 bg-slate-950/95 p-7 shadow-[0_0_20px_rgba(6,182,212,0.25)] transition-all hover:-translate-y-2 hover:border-yellow-400 hover:shadow-[0_0_30px_rgba(250,204,21,0.5)] {{ $isFeatured ? 'ring-2 ring-fuchsia-500' : '' }}">
                    @if($isFeatured)
                        <div class="absolute -top-3.5 right-4 bg-fuchsia-600 px-3 py-0.5 text-xs font-black uppercase text-white shadow-[0_0_10px_rgba(217,70,239,0.8)]">
                            ⚡ {{ $t('subbase::plan.pricing.most_popular', 'Most Popular') }}
                        </div>
                    @endif

                    <div>
                        <div class="text-xs text-cyan-400">[SYSTEM.PLAN_ID: {{ $plan->slug }}]</div>
                        <div class="mt-2 flex items-start justify-between gap-4 border-b border-cyan-400/30 pb-5">
                            <div>
                                <p class="mb-2 text-[10px] uppercase tracking-[0.25em] text-fuchsia-400">NODE_{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                                <h3 class="text-2xl font-black uppercase tracking-wider text-yellow-400">{{ $plan->name }}</h3>
                            </div>
                            <span class="text-2xl text-cyan-400">↗</span>
                        </div>
                        <p class="mt-4 min-h-12 text-xs font-medium leading-6 text-slate-400">{{ $plan->description }}</p>

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
