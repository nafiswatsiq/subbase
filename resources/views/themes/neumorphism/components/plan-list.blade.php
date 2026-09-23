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

<div class="subbase-plan-list bg-[#e0e5ec] py-20 text-slate-800 font-sans">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="mb-4 text-xs font-bold uppercase tracking-[0.3em] text-indigo-600">{{ $label }}</p>
            <h2 class="text-4xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-6xl">
                {{ $title }}
            </h2>
            <p class="mx-auto mt-5 max-w-xl text-base leading-7 text-slate-700 sm:text-lg">
                {{ $subtitle }}
            </p>
        </div>

        @if($plans->isEmpty())
            <div class="mt-14 rounded-2xl bg-[#e0e5ec] p-10 text-center text-sm text-slate-700 shadow-[inset_4px_4px_8px_#b8c2d1,inset_-4px_-4px_8px_#ffffff]">
                {{ $t('subbase::plan.pricing.no_plans', 'No active plans available at the moment.') }}
            </div>
        @else
            @if($intervals->count() > 1)
                <div class="mt-14 flex justify-center" role="tablist">
                    <div class="flex rounded-full bg-[#e0e5ec] p-1.5 shadow-[inset_4px_4px_8px_#b8c2d1,inset_-4px_-4px_8px_#ffffff]">
                        @foreach($intervals as $index => $interval)
                            <button type="button" role="tab" aria-selected="{{ $index === 0 ? 'true' : 'false' }}" data-target-interval="{{ $interval }}" class="interval-tab rounded-full px-5 py-2.5 text-xs font-bold uppercase transition {{ $index === 0 ? 'bg-[#e0e5ec] text-indigo-600 shadow-[4px_4px_8px_#b8c2d1,-4px_-4px_8px_#ffffff]' : 'text-slate-600' }}">
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

                <div data-interval="{{ $plan->invoice_interval }}" @if($intervals->count() > 1 && $plan->invoice_interval !== $intervals[0]) style="display: none;" @endif class="plan-card relative flex min-h-[32rem] flex-col justify-between rounded-[2.25rem] bg-[#e0e5ec] p-8 transition-all hover:-translate-y-1.5 {{ $isFeatured ? 'shadow-[16px_16px_32px_#b8c2d1,-16px_-16px_32px_#ffffff,0_0_0_2px_#4f46e5]' : 'shadow-[14px_14px_28px_#b8c2d1,-14px_-14px_28px_#ffffff]' }}">
                    @if($isFeatured)
                        <div class="absolute -top-4 right-6 rounded-full bg-[#e0e5ec] px-4 py-1.5 text-xs font-extrabold text-indigo-600 shadow-[4px_4px_8px_#b8c2d1,-4px_-4px_8px_#ffffff]">
                            {{ $t('subbase::plan.pricing.most_popular', 'Most Popular') }}
                        </div>
                    @endif

                    <div>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="mb-3 text-[10px] font-bold uppercase tracking-[0.25em] text-indigo-600">Plan {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                                <h3 class="text-2xl font-extrabold text-slate-900">{{ $plan->name }}</h3>
                            </div>
                            <span class="grid h-11 w-11 place-items-center rounded-full bg-[#e0e5ec] text-indigo-600 font-extrabold shadow-[4px_4px_8px_#b8c2d1,-4px_-4px_8px_#ffffff]">↗</span>
                        </div>
                        <p class="mt-4 min-h-12 text-sm leading-6 text-slate-600 font-medium">{{ $plan->description }}</p>

                        <div class="mt-7 flex items-end justify-between gap-2 border-y border-slate-300/40 py-5">
                            <span class="text-4xl font-black text-slate-900">
                                {{ $pricing['final_price'] }}
                            </span>
                            <span class="text-sm font-semibold text-slate-600">/ {{ $plan->invoice_period > 1 ? $plan->invoice_period . ' ' : '' }}{{ $tc("subbase::plan.pricing.interval.{$plan->invoice_interval}", $plan->invoice_period, $plan->invoice_interval) }}</span>
                        </div>

                        @if($pricing['discount_info'] !== null)
                            <div class="mt-4 flex items-center gap-2">
                                <span class="text-sm text-slate-500 line-through">
                                    {{ $pricing['original_price'] }}
                                </span>
                                <span class="rounded-full bg-[#e0e5ec] px-3 py-1 text-xs font-bold text-indigo-600 shadow-[inset_2px_2px_4px_#b8c2d1,inset_-2px_-2px_4px_#ffffff]">
                                    {{ $pricing['discount_info']['formatted_value'] }} {{ $t('subbase::plan.pricing.off', 'OFF') }}
                                </span>
                            </div>
                        @endif

                        <ul class="mt-7 space-y-3.5">
                            @foreach($plan->features as $feature)
                                <li class="flex items-center gap-3 text-sm text-slate-700 font-medium">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#e0e5ec] text-indigo-600 font-bold text-xs shadow-[inset_2px_2px_4px_#b8c2d1,inset_-2px_-2px_4px_#ffffff]">✓</span>
                                    <span>{{ $feature->name }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mt-8">
                        <a href="{{ $checkoutUrl }}" class="block w-full rounded-2xl bg-[#4f46e5] py-4 text-center font-bold text-white shadow-[6px_6px_14px_#b8c2d1,-6px_-6px_14px_#ffffff] transition-all hover:bg-[#4338ca] active:shadow-[inset_3px_3px_6px_#312e81,inset_-3px_-3px_6px_#6366f1]">
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
                item.classList.toggle('text-indigo-600', active);
                item.classList.toggle('shadow-[4px_4px_8px_#b8c2d1,-4px_-4px_8px_#ffffff]', active);
                item.classList.toggle('text-slate-600', !active);
            });
            cards.forEach(card => card.style.display = card.dataset.interval === interval ? 'flex' : 'none');
        }));
    });
</script>
@endif
