@props([
    'plans' => null,
    'locale' => null,
    'subscribeRoute' => null,
    'label' => null,
    'title' => null,
    'subtitle' => null,
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
    $tc = function ($key, $number, $default) {
        $translation = trans_choice($key, $number);

        return $translation === $key ? $default : $translation;
    };

    $label = $label ?? $t('subbase::plan.pricing.label', 'Pricing');
    $title = $title ?? $t('subbase::plan.pricing.title', 'Simple, transparent pricing');
    $subtitle = $subtitle ?? $t('subbase::plan.pricing.subtitle', 'Choose the plan that fits your needs. No hidden fees.');

    $intervals = $plans->pluck('invoice_interval')->filter()->unique()->values();
@endphp

<div class="subbase-plan-list relative overflow-hidden bg-[#05070d] py-20 text-white font-mono">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="relative mx-auto max-w-3xl text-center">
            <p class="mb-4 text-xs font-bold uppercase tracking-[0.35em] text-cyan-400">[ {{ $label }} ]</p>
            <h2 class="text-2xl font-black uppercase leading-none tracking-wider text-yellow-400 sm:text-5xl">
                // {{ $title }} \\
            </h2>
            <p class="mx-auto mt-6 max-w-xl text-sm font-medium leading-6 text-cyan-300">
                {{ $subtitle }}
            </p>
        </div>

        @if($plans->isEmpty())
            <div class="relative mt-14 border border-cyan-400/40 bg-slate-900/90 p-8 text-center text-sm font-medium text-cyan-300">
                {{ $t('subbase::plan.pricing.no_plans', 'No active plans available at the moment.') }}
            </div>
        @else
            @if($intervals->count() > 1)
                <div class="relative mt-14 flex justify-center" role="tablist">
                    <div class="flex border border-cyan-400/40 bg-slate-900 p-1 shadow-[0_0_8px_rgba(6,182,212,0.15)]">
                        @foreach($intervals as $index => $interval)
                            <button type="button" role="tab" aria-selected="{{ $index === 0 ? 'true' : 'false' }}" data-target-interval="{{ $interval }}" class="interval-tab border border-transparent px-4 py-2 text-xs font-bold uppercase tracking-wider {{ $index === 0 ? 'border-yellow-400 bg-yellow-400 text-black' : 'text-cyan-300' }}">
                                {{ $tc("subbase::plan.pricing.interval.{$interval}", 1, ucfirst($interval)) }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

        <div class="relative {{ $intervals->count() > 1 ? 'mt-8' : 'mt-14' }} grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($plans as $plan)
                @php
                    $pricing = \Nafiswatsiq\Subbase\Helpers\PlanPriceHelper::formatWithDiscounts($plan, $currency);
                    if ($subscribeRoute) {
                        $checkoutUrl = route($subscribeRoute, ['plan' => $plan->slug]);
                    } elseif (\Illuminate\Support\Facades\Route::has('subbase-payment.checkout')) {
                        $checkoutUrl = route('subbase-payment.checkout', ['plan' => $plan->slug]);
                    } else {
                        $checkoutUrl = \Illuminate\Support\Facades\Route::has('subbase.subscribe')
                            ? route('subbase.subscribe', ['plan' => $plan->slug])
                            : '#';
                    }
                    $isFeatured = (bool) ($plan->featured ?? false);
                @endphp

                <div data-interval="{{ $plan->invoice_interval }}" @if($intervals->count() > 1 && $plan->invoice_interval !== $intervals[0]) style="display: none;" @endif class="plan-card relative flex min-h-[32rem] flex-col justify-between border border-cyan-400/40 bg-slate-900/90 p-7 shadow-[0_0_12px_rgba(6,182,212,0.15)] transition-all hover:-translate-y-1 hover:border-yellow-400 hover:shadow-[0_0_20px_rgba(250,204,21,0.3)] {{ $isFeatured ? 'ring-2 ring-fuchsia-500' : '' }}">
                    @if($isFeatured)
                        <div class="absolute -top-3.5 right-4 bg-fuchsia-600 px-3 py-0.5 text-xs font-bold uppercase text-white shadow-[0_0_6px_rgba(217,70,239,0.6)]">
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
                        <p class="mt-4 min-h-12 text-sm font-medium leading-6 text-slate-200">{{ $plan->description }}</p>

                        <div class="mt-6 flex items-baseline gap-2 border-b border-cyan-400/30 pb-4">
                            <span class="text-4xl font-black text-white drop-shadow-[0_0_8px_rgba(255,255,255,0.6)]">
                                {{ $pricing['final_price'] }}
                            </span>
                            <span class="text-xs uppercase text-cyan-400">/ {{ $plan->invoice_period > 1 ? $plan->invoice_period . ' ' : '' }}{{ $tc("subbase::plan.pricing.interval.{$plan->invoice_interval}", $plan->invoice_period, $plan->invoice_interval) }}</span>
                        </div>

                        @if($pricing['discount_info'] !== null)
                            <div class="mb-4 flex items-center gap-2">
                                <span class="text-xs text-cyan-300 line-through">
                                    {{ $pricing['original_price'] }}
                                </span>
                                <span class="border border-yellow-400 bg-yellow-400/20 px-2 py-1 text-xs font-bold uppercase text-yellow-400">
                                    {{ $pricing['discount_info']['formatted_value'] }} {{ $t('subbase::plan.pricing.off', 'OFF') }}
                                </span>
                            </div>
                        @endif

                        <ul class="mt-6 space-y-2.5">
                            @foreach($plan->features as $feature)
                                <li class="flex items-center gap-2 text-sm text-cyan-200">
                                    <span class="text-yellow-400">&gt;&gt;</span>
                                    <span>{{ $feature->name }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mt-8">
                        <a href="{{ $checkoutUrl }}" class="block w-full border-2 border-yellow-400 bg-yellow-400 py-3.5 text-center font-bold uppercase tracking-wide text-black shadow-[0_0_10px_rgba(250,204,21,0.4)] transition-all hover:bg-cyan-400 hover:border-cyan-400 hover:shadow-[0_0_15px_rgba(6,182,212,0.5)]">
                            INITIALIZE_SUB //
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

@if($intervals->count() > 1)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabs = document.querySelectorAll('.interval-tab');
        const cards = document.querySelectorAll('.plan-card');
        tabs.forEach(tab => tab.addEventListener('click', function () {
            const interval = tab.dataset.targetInterval;
            tabs.forEach(item => {
                const active = item.dataset.targetInterval === interval;
                item.setAttribute('aria-selected', active ? 'true' : 'false');
                item.classList.toggle('border-yellow-400', active);
                item.classList.toggle('bg-yellow-400', active);
                item.classList.toggle('text-black', active);
                item.classList.toggle('text-cyan-300', !active);
            });
            cards.forEach(card => card.style.display = card.dataset.interval === interval ? 'flex' : 'none');
        }));
    });
</script>
@endif
