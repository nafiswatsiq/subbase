@props([
    'subscribeRoute' => null,
])
@php
    $t = function($key, $default) {
        $trans = __($key);
        return $trans === $key ? $default : $trans;
    };
    $tc = function($key, $number, $default) {
        $trans = trans_choice($key, $number);
        return $trans === $key ? $default : $trans;
    };

    $plans = config('subbase.models.plan', \Nafiswatsiq\Subbase\Models\Plan::class)::active()
        ->orderBy('sort_order')
        ->get();

    $intervals = $plans->pluck('invoice_interval')->filter()->unique()->values();
@endphp

<div class="max-w-8xl mx-auto px-6 py-16">

    {{-- Header --}}
    <div class="text-center mb-12">
        <p class="text-xs font-semibold tracking-widest text-blue-600 uppercase mb-3">
            {{ $t('subbase::plan.pricing.label', 'Pricing') }}
        </p>
        <h2 class="text-3xl font-bold text-gray-900 tracking-tight">
            {{ $t('subbase::plan.pricing.title', 'Pricing Plans') }}
        </h2>
        <p class="mt-3 text-base text-gray-500">
            {{ $t('subbase::plan.pricing.subtitle', 'Choose the plan that fits your needs.') }}
        </p>
    </div>

    @if($plans->isEmpty())
        <div class="text-center text-gray-400 py-16 text-sm">
            {{ $t('subbase::plan.pricing.no_plans', 'No active plans available at the moment.') }}
        </div>
    @else

        {{-- Interval toggle --}}
        @if($intervals->count() > 1)
            <div class="flex justify-center mb-10">
                <div class="inline-flex bg-gray-100 rounded-lg p-1 gap-0.5" role="tablist">
                    @foreach($intervals as $index => $interval)
                        <button
                            role="tab"
                            aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                            data-target-interval="{{ $interval }}"
                            class="tab-btn px-5 py-2 text-sm font-medium rounded-md transition-all duration-150
                                   {{ $index === 0
                                       ? 'bg-white text-gray-900 shadow-sm'
                                       : 'text-gray-500 hover:text-gray-700' }}">
                            {{ $tc("subbase::plan.pricing.interval.{$interval}", 1, ucfirst($interval)) }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 items-stretch">
            @foreach($plans as $plan)
                @php
                    $isFeatured = $plan->featured;
                    $price = $plan->getPriceForLocale(app()->getLocale());
                    $currency = \Nafiswatsiq\Subbase\Models\Plan::currencyFromLocale(app()->getLocale());
                    $formattedPrice = \Illuminate\Support\Number::currency($price, $currency, app()->getLocale());

                    if ($subscribeRoute) {
                        $subscribeUrl = route($subscribeRoute, ['plan' => $plan->slug]);
                    } else {
                        $subscribeUrl = \Illuminate\Support\Facades\Route::has('subbase.subscribe')
                            ? route('subbase.subscribe', ['plan' => $plan->slug])
                            : '#';
                    }
                @endphp

                <div
                    class="plan-card min-w-[300px] relative flex flex-col rounded-xl border bg-white p-6 transition-shadow duration-200
                           {{ $isFeatured
                               ? 'border-blue-500 border-2 shadow-md hover:shadow-lg'
                               : 'border-gray-200 border hover:shadow-md' }}"
                    data-interval="{{ $plan->invoice_interval }}"
                    @if($intervals->count() > 1 && $plan->invoice_interval !== $intervals[0])
                        style="display: none;"
                    @endif>

                    {{-- Most Popular badge --}}
                    @if($isFeatured)
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span class="inline-block bg-blue-600 text-white text-center text-[10px] font-semibold tracking-wider uppercase px-3 py-1 rounded-full">
                                {{ $t('subbase::plan.pricing.most_popular', 'Most Popular') }}
                            </span>
                        </div>
                    @endif

                    {{-- Plan name & description --}}
                    <div class="mb-5">
                        <h3 class="text-base font-semibold text-gray-900">{{ $plan->name }}</h3>
                        @if($plan->description)
                            <p class="mt-1 text-sm text-gray-500 leading-relaxed">{{ $plan->description }}</p>
                        @endif
                    </div>

                    {{-- Price --}}
                    <div class="flex items-baseline gap-1 pb-5 mb-5 border-b border-gray-100">
                        <span class="text-4xl font-bold tracking-tight text-gray-900">{{ $formattedPrice }}</span>
                        @if($plan->invoice_interval)
                            <span class="text-sm text-gray-400 font-normal self-end pb-0.5">
                                / {{ $plan->invoice_period > 1 ? $plan->invoice_period . ' ' : '' }}{{ $tc("subbase::plan.pricing.interval.{$plan->invoice_interval}", $plan->invoice_period, $plan->invoice_interval) }}
                            </span>
                        @endif
                    </div>

                    {{-- Features --}}
                    <ul role="list" class="flex flex-col gap-3 mb-6 flex-1">
                        @forelse($plan->features as $feature)
                            <li class="flex items-start gap-2.5 text-sm text-gray-600">
                                <span class="mt-0.5 flex-shrink-0 w-4 h-4 rounded-full bg-blue-50 flex items-center justify-center" aria-hidden="true">
                                    <svg class="w-2.5 h-2.5 text-blue-600" viewBox="0 0 8 8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="1,4.5 3,6.5 7,1.5"/>
                                    </svg>
                                </span>
                                <span>
                                    <span class="font-medium text-gray-800">{{ $feature->name }}</span>
                                    @if($feature->description)
                                        <span class="block text-xs text-gray-400 mt-0.5">{{ $feature->description }}</span>
                                    @endif
                                </span>
                            </li>
                        @empty
                            <li class="text-sm text-gray-400 italic">
                                {{ $t('subbase::plan.pricing.no_features', 'No features listed.') }}
                            </li>
                        @endforelse
                    </ul>

                    {{-- CTA --}}
                    <a href="{{ $subscribeUrl }}"
                       class="mt-auto block w-full text-center text-sm font-semibold py-2.5 px-4 rounded-lg transition-colors duration-150
                              {{ $isFeatured
                                  ? 'bg-blue-600 text-white hover:bg-blue-500'
                                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        {{ $t('subbase::plan.pricing.subscribe_button', 'Get started') }}
                    </a>

                </div>
            @endforeach
        </div>

    @endif
</div>

@if(!empty($intervals) && $intervals->count() > 1)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabs = document.querySelectorAll('.tab-btn');
        const cards = document.querySelectorAll('.plan-card');

        function switchTab(targetInterval) {
            tabs.forEach(tab => {
                const isActive = tab.getAttribute('data-target-interval') === targetInterval;
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
                if (isActive) {
                    tab.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
                    tab.classList.remove('text-gray-500', 'hover:text-gray-700');
                } else {
                    tab.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
                    tab.classList.add('text-gray-500', 'hover:text-gray-700');
                }
            });

            cards.forEach(card => {
                if (card.getAttribute('data-interval') === targetInterval) {
                    card.style.display = 'flex';
                    card.style.opacity = '0';
                    requestAnimationFrame(() => {
                        card.style.transition = 'opacity 0.2s ease';
                        card.style.opacity = '1';
                    });
                } else {
                    card.style.display = 'none';
                }
            });
        }

        tabs.forEach(tab => {
            tab.addEventListener('click', () => switchTab(tab.getAttribute('data-target-interval')));
        });
    });
</script>
@endif