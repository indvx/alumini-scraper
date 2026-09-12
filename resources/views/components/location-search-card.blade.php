@props([
    'locationQuery' => old('location_query', session('locationQuery', '')),
    'country' => old('country', session('country', '')),
    'state' => old('state', session('state', '')),
    'city' => old('city', session('city', '')),
    'searchResult' => session('searchResult'),
    'searchError' => session('searchError'),
])

@php
    if (empty($country) && !empty($locationQuery)) {
        $parts = array_map('trim', explode(',', $locationQuery));
        if (count($parts) >= 3) {
            $city = $city ?: $parts[0];
            $state = $state ?: $parts[1];
            $country = $country ?: $parts[2];
        } elseif (count($parts) == 2) {
            $state = $state ?: $parts[0];
            $country = $country ?: $parts[1];
        } elseif (count($parts) == 1) {
            $country = $country ?: $parts[0];
        }
    }
@endphp

<div id="locationSearchCardContainer"
    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-1 md:p-4 shadow-sm">
    <div class="max-w-3xl space-y-1">
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white">
            Discover Educational Institutions
        </h1>
        <p class="text-slate-600 dark:text-slate-400 text-sm">
            Select Country &amp; State to discover and ingest schools, colleges, universities, and kindergartens via
            OpenStreetMap.
        </p>
    </div>

    <!-- Location Search Form -->
    <form action="{{ route('institutions.search') }}" method="POST" class="mt-2 space-y-2" id="cardLocationSearchForm">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-start">
            <!-- Country Field (Mandatory) -->
            <div class="relative">
                <label for="cardCountryInput"
                    class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Country <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="cardCountryInput" name="country" value="{{ $country }}" required
                    autocomplete="off" placeholder="Select Country..."
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 transition-all" />
                <input type="hidden" id="cardCountryId" name="country_id" />

                <!-- Suggestions Dropdown -->
                <div id="cardCountrySuggestions"
                    class="absolute z-30 left-0 right-0 mt-1 max-h-48 overflow-y-auto bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl hidden divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                </div>
            </div>

            <!-- State Field (Mandatory) -->
            <div id="cardStateContainer" class="relative {{ empty($country) ? 'hidden' : '' }}">
                <label for="cardStateInput"
                    class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    State / Province <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="cardStateInput" name="state" value="{{ $state }}"
                    {{ !empty($country) ? 'required' : '' }} autocomplete="off" placeholder="Select State..."
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 transition-all" />
                <input type="hidden" id="cardStateId" name="state_id" />

                <!-- Suggestions Dropdown -->
                <div id="cardStateSuggestions"
                    class="absolute z-30 left-0 right-0 mt-1 max-h-48 overflow-y-auto bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl hidden divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                </div>
            </div>

            <!-- City Field (Optional) -->
            <div id="cardCityContainer" class="relative {{ empty($state) ? 'hidden' : '' }}">
                <label for="cardCityInput"
                    class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    City <span class="text-slate-400 font-normal lowercase">(optional)</span>
                </label>
                <input type="text" id="cardCityInput" name="city" value="{{ $city }}" autocomplete="off"
                    placeholder="Select City..."
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 transition-all" />
                <input type="hidden" id="cardCityId" name="city_id" />

                <!-- Suggestions Dropdown -->
                <div id="cardCitySuggestions"
                    class="absolute z-30 left-0 right-0 mt-1 max-h-48 overflow-y-auto bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl hidden divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                </div>
            </div>
        </div>

        <div
            class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2 border-t border-slate-100 dark:border-slate-800">
            <div class="flex items-center text-xs text-slate-500 dark:text-slate-400">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="force_refresh" value="1"
                        class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                    <span>Force fresh query from OpenStreetMap (bypass cached results)</span>
                </label>
            </div>
            <button type="submit" id="cardLocationSubmitBtn"
                class="w-full sm:w-auto bg-rose-600 hover:bg-rose-700 text-white px-6 py-3 rounded-xl font-semibold text-sm transition-all shadow-sm flex items-center justify-center gap-2 whitespace-nowrap">
                <svg id="cardLocationSearchIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <x-loader id="cardLocationSpinner" class="hidden text-white" />
                <span id="cardLocationSubmitText">Search Location</span>
            </button>
        </div>
    </form>

    <!-- Search Result Notification -->
    @if (isset($searchResult) && $searchResult)
        <div
            class="mt-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center justify-between">
            <div>
                <strong>Search Completed for
                    "{{ is_array($searchResult['search']) ? $searchResult['search']['query'] : $searchResult['search']->query }}":</strong>
                Found <strong>{{ count($searchResult['institutions'] ?? []) }}</strong> institutions
                ({{ is_array($searchResult['search']) ? $searchResult['search']['display_name'] : $searchResult['search']->display_name }}).
            </div>
            <span
                class="px-2.5 py-1 rounded-md text-xs font-bold {{ !empty($searchResult['cached']) ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                {{ !empty($searchResult['cached']) ? 'Cached' : 'Live Query' }}
            </span>
        </div>
    @endif

    @if (isset($searchError) && $searchError)
        <div
            class="mt-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm">
            <strong>Ingestion Error:</strong> {{ $searchError }}
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.initLocationCascade === 'function') {
            window.initLocationCascade({
                countryInputId: 'cardCountryInput',
                countryIdInputId: 'cardCountryId',
                countrySuggestionsId: 'cardCountrySuggestions',

                stateContainerId: 'cardStateContainer',
                stateInputId: 'cardStateInput',
                stateIdInputId: 'cardStateId',
                stateSuggestionsId: 'cardStateSuggestions',
                stateRequired: true,

                cityContainerId: 'cardCityContainer',
                cityInputId: 'cardCityInput',
                cityIdInputId: 'cardCityId',
                citySuggestionsId: 'cardCitySuggestions',

                hoverClass: 'hover:bg-rose-50 dark:hover:bg-slate-700/80',
                containerId: 'locationSearchCardContainer',
            });
        }

        const cardForm = document.getElementById('cardLocationSearchForm');
        if (cardForm) {
            cardForm.addEventListener('submit', function() {
                const btn = document.getElementById('cardLocationSubmitBtn');
                const icon = document.getElementById('cardLocationSearchIcon');
                const spinner = document.getElementById('cardLocationSpinner');
                const text = document.getElementById('cardLocationSubmitText');

                if (btn) {
                    btn.disabled = true;
                    btn.classList.add('opacity-75', 'cursor-not-allowed', 'pointer-events-none');
                }
                if (icon) icon.classList.add('hidden');
                if (spinner) spinner.classList.remove('hidden');
                if (text) text.textContent = 'Searching...';
            });
        }
    });
</script>
