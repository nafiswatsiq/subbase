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

<div class="subbase-plan-list relative overflow-hidden bg-[#f4efe6] py-16 text-black font-sans">
    <div class="absolute inset-x-0 top-0 h-3 bg-black"></div>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="mb-4 text-xs font-black uppercase tracking-[0.3em] text-pink-600">{{ $label }}</p>
            <h2 class="text-4xl font-black uppercase leading-[0.95] tracking-tight text-black sm:text-6xl">
                {{ $title }}
            </h2>
            <p class="mx-auto mt-6 max-w-xl text-base font-bold leading-7 text-gray-700 sm:text-lg">
                {{ $subtitle }}
            </p>
        </div>

        @if($plans->isEmpty())
            <div class="mt-14 border-4 border-black bg-white p-10 text-center text-sm font-black uppercase">
                {{ $t('subbase::plan.pricing.no_plans', 'No active plans available at the moment.') }}
            </div>
        @else
            @if($intervals->count() > 1)
                <div class="mt-14 flex justify-center" role="tablist">
                    <div class="flex border-4 border-black bg-white p-1">
                        @foreach($intervals as $index => $interval)
                            <button type="button" role="tab" aria-selected="{{ $index === 0 ? 'true' : 'false' }}" data-target-interval="{{ $interval }}" class="interval-tab border-2 border-black px-4 py-2 text-xs font-black uppercase {{ $index === 0 ? 'bg-yellow-300 text-black' : 'bg-white text-black' }}">
                                {{ $tc("subbase::plan.pricing.interval.{$interval}", 1, ucfirst($interval)) }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

        <div class="{{ $intervals->count() > 1 ? 'mt-8' : 'mt-14' }} grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
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

                <div data-interval="{{ $plan->invoice_interval }}" @if($intervals->count() > 1 && $plan->invoice_interval !== $intervals[0]) style="display: none;" @endif class="plan-card relative flex min-h-[32rem] flex-col justify-between border-4 border-black p-7 transition-transform hover:-translate-y-2 {{ $isFeatured ? 'bg-[#ffd447] shadow-[10px_10px_0px_0px_rgba(0,0,0,1)]' : 'bg-white shadow-[7px_7px_0px_0px_rgba(0,0,0,1)]' }}">
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
                            <span class="pb-1 text-xs font-black uppercase text-gray-700">/ {{ $plan->invoice_period > 1 ? $plan->invoice_period . ' ' : '' }}{{ $tc("subbase::plan.pricing.interval.{$plan->invoice_interval}", $plan->invoice_period, $plan->invoice_interval) }}</span>
                        </div>

                        @if($pricing['discount_info'] !== null)
                            <div class="mt-2 flex items-center gap-2">
                                <div class="inline-block border-2 border-black bg-pink-300 px-2 py-0.5 text-xs font-black uppercase">
                                    {{ $t('subbase::plan.pricing.was', 'Was') }} {{ $pricing['original_price'] }}
                                </div>
                                <div class="inline-block border-2 border-black bg-yellow-300 px-2 py-0.5 text-xs font-black uppercase">
                                    {{ $pricing['discount_info']['formatted_value'] }} {{ $t('subbase::plan.pricing.off', 'OFF') }}
                                </div>
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
                        <a href="{{ $checkoutUrl }}" class="block w-full border-4 border-black bg-black py-4 text-center font-black uppercase tracking-wider text-white transition-all hover:bg-pink-400 hover:text-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:translate-x-1 active:translate-y-1 active:shadow-none">
                            {{ $t('subbase::plan.pricing.subscribe_button', 'Get started') }}
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
                item.classList.toggle('bg-yellow-300', active);
                item.classList.toggle('bg-white', !active);
            });
            cards.forEach(card => {
                card.style.display = card.dataset.interval === interval ? 'flex' : 'none';
            });
        }));
    });
</script>
@endif
