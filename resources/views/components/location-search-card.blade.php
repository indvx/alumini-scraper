@props([
    'locationQuery' => old('location_query', session('locationQuery', '')),
    'searchResult' => session('searchResult'),
    'searchError' => session('searchError'),
])

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 md:p-8 shadow-sm">
    <div class="max-w-3xl space-y-2">
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white">
            Discover Educational Institutions
        </h1>
        <p class="text-slate-600 dark:text-slate-400 text-sm">
            Search and ingest schools, colleges, universities, and kindergartens by location via OpenStreetMap.
        </p>
    </div>

    <!-- Location Search Form -->
    <form action="{{ route('institutions.search') }}" method="POST" class="mt-4 max-w-3xl space-y-3">
        @csrf
        <div class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="location_query" value="{{ $locationQuery }}"
                placeholder="Enter location (e.g. Dallas County, Texas or London, UK)..." required
                class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-500 text-sm">
            <button type="submit"
                class="bg-rose-600 hover:bg-rose-700 text-white px-6 py-3 rounded-xl font-semibold text-sm transition-all shadow-sm flex items-center justify-center gap-2 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Search Location
            </button>
        </div>
        <div class="flex items-center text-xs text-slate-500 dark:text-slate-400">
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="force_refresh" value="1"
                    class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                <span>Force fresh query from OpenStreetMap (bypass cached results)</span>
            </label>
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
