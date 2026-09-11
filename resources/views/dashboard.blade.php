<x-layouts.app title="Dashboard">
    <div class="space-y-6">
        <!-- Header & Search Box -->
        <x-location-search-card />

        <!-- Metrics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Recent Searches -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Recent Location Searches</h3>
                @if(count($recentSearches) > 0)
                <div class="divide-y divide-slate-100 dark:divide-slate-800 h-[30vh] overflow-y-auto">
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
                <div class="divide-y divide-slate-100 dark:divide-slate-800 h-[30vh] overflow-y-auto">
                    @foreach($recentInstitutions as $inst)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <a href="{{ route('institutions.show', $inst->id) }}" class="text-sm font-bold text-slate-900 dark:text-white hover:text-rose-600">
                                {{ $inst->name }}
                            </a>
                            <span class="text-xs text-slate-500 dark:text-slate-400 block capitalize">{{ $inst->type }} &bull; {{ $inst->city ?: $inst->state }}</span>
                        </div>
                        <a href="{{ route('institutions.show', $inst->id) }}" class="text-xs font-semibold text-rose-600 hover:underline">
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