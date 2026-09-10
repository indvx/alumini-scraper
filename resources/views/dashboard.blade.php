<x-layouts.app title="Dashboard">
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white">Application Dashboard</h1>
            <p class="text-slate-600 dark:text-slate-400 text-sm mt-1">Overview of ingested alumni educational institution records and OpenStreetMap geocoding activity.</p>
        </div>

        <!-- Header & Search Box -->
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
            <form action="{{ route('public.institutions.search') }}" method="POST" class="mt-6 max-w-3xl space-y-3">
                @csrf
                <div class="flex flex-col sm:flex-row gap-3">
                    <input type="text"
                        name="location_query"
                        value="{{ old('location_query', $locationQuery ?? '') }}"
                        placeholder="Enter location (e.g. Dallas County, Texas or London, UK)..."
                        required
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-500 text-sm">
                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-6 py-3 rounded-xl font-semibold text-sm transition-all shadow-sm flex items-center justify-center gap-2 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Search Location
                    </button>
                </div>
                <div class="flex items-center text-xs text-slate-500 dark:text-slate-400">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="force_refresh" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                        <span>Force fresh query from OpenStreetMap (bypass cached results)</span>
                    </label>
                </div>
            </form>

            <!-- Search Result Notification -->
            @if(isset($searchResult))
            <div class="mt-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center justify-between">
                <div>
                    <strong>Search Completed for "{{ $searchResult['search']->query }}":</strong>
                    Found <strong>{{ count($searchResult['institutions']) }}</strong> institutions ({{ $searchResult['search']->display_name }}).
                </div>
                <span class="px-2.5 py-1 rounded-md text-xs font-bold {{ $searchResult['cached'] ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                    {{ $searchResult['cached'] ? 'Cached' : 'Live Query' }}
                </span>
            </div>
            @endif

            @if(isset($searchError))
            <div class="mt-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm">
                <strong>Ingestion Error:</strong> {{ $searchError }}
            </div>
            @endif
        </div>

        <!-- Metrics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Total Institutions</span>
                <span class="text-3xl font-extrabold text-slate-900 dark:text-white mt-2 block">{{ number_format($totalInstitutions) }}</span>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Locations Searched</span>
                <span class="text-3xl font-extrabold text-slate-900 dark:text-white mt-2 block">{{ number_format($totalSearches) }}</span>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Schools & Colleges</span>
                <span class="text-3xl font-extrabold text-rose-600 mt-2 block">
                    {{ number_format(($typeBreakdown['school'] ?? 0) + ($typeBreakdown['college'] ?? 0)) }}
                </span>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Universities</span>
                <span class="text-3xl font-extrabold text-amber-600 mt-2 block">
                    {{ number_format($typeBreakdown['university'] ?? 0) }}
                </span>
            </div>
        </div>

        <!-- Content Split Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Searches -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Recent Location Searches</h3>
                @if(count($recentSearches) > 0)
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($recentSearches as $s)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <strong class="text-sm text-slate-900 dark:text-white block">{{ $s->query }}</strong>
                            <span class="text-xs text-slate-500 dark:text-slate-400 block truncate max-w-sm">{{ $s->display_name }}</span>
                        </div>
                        <span class="text-xs font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-md">
                            {{ number_format($s->total_found) }} found
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-500">No searches conducted yet.</p>
                @endif
            </div>

            <!-- Recent Ingested Institutions -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Recently Discovered Institutions</h3>
                @if(count($recentInstitutions) > 0)
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($recentInstitutions as $inst)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <a href="{{ route('public.institutions.show', $inst->id) }}" class="text-sm font-bold text-slate-900 dark:text-white hover:text-rose-600">
                                {{ $inst->name }}
                            </a>
                            <span class="text-xs text-slate-500 dark:text-slate-400 block capitalize">{{ $inst->type }} &bull; {{ $inst->city ?: $inst->state }}</span>
                        </div>
                        <a href="{{ route('public.institutions.show', $inst->id) }}" class="text-xs font-semibold text-rose-600 hover:underline">
                            View
                        </a>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-500">No institution records found yet.</p>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>