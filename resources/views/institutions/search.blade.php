<x-layouts.app title="Institution">
    <div class="flex h-full w-full flex-1 flex-col gap-6">

        <!-- Filter Bar & Results Catalog -->
        <div class="space-y-2">
            <!-- Catalog Header -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">
                        Institutions List
                    </h2>
                </div>
                <a href="{{ route('institutions.export', request()->all()) }}"
                    class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 text-sm font-semibold flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export CSV
                </a>
            </div>
            <!-- Filter Form -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                <form action="{{ route('institutions.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @if(request('search_id'))
                    <input type="hidden" name="search_id" value="{{ request('search_id') }}">
                    @endif
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Search Name / Address</label>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name, city, state..."
                            class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Type</label>
                        <select name="type" class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                            <option value="all">All Types</option>
                            <option value="school" {{ ($filters['type'] ?? '') == 'school' ? 'selected' : '' }}>School</option>
                            <option value="university" {{ ($filters['type'] ?? '') == 'university' ? 'selected' : '' }}>University</option>
                            <option value="college" {{ ($filters['type'] ?? '') == 'college' ? 'selected' : '' }}>College</option>
                            <option value="kindergarten" {{ ($filters['type'] ?? '') == 'kindergarten' ? 'selected' : '' }}>Kindergarten</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Postcode</label>
                        <input type="text" name="postcode" value="{{ $filters['postcode'] ?? '' }}" placeholder="ZIP / Postcode..."
                            class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition-all shadow-sm">
                            Filter
                        </button>
                        <a href="{{ route('institutions.index') }}" class="py-2 px-4 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-semibold transition-colors">Reset</a>
                    </div>
                </form>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Page {{ $institutions->currentPage() }} of {{ $institutions->lastPage() }}</p>
            </div>
            <!-- Grid of Cards -->
            @if($institutions->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 h-[54vh] overflow-y-scroll">
                @foreach($institutions as $inst)
                <div id="{{ $inst->id }}" class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-3 shadow-sm hover:border-slate-300 dark:hover:border-slate-900 flex flex-col justify-between space-y-1 transition-all">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-0.5 rounded text-xs font-extrabold uppercase tracking-wider 
                                        {{ $inst->type == 'university' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : '' }}
                                        {{ $inst->type == 'college' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' : '' }}
                                        {{ $inst->type == 'school' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' : '' }}
                                        {{ $inst->type == 'kindergarten' ? 'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300' : '' }}">
                                {{ $inst->type }}
                            </span>
                            @if($inst->osm_id)
                            <span class="text-[10px] text-slate-400 font-mono">OSM #{{ $inst->osm_id }}</span>
                            @endif
                        </div>

                        <h3 class="text-base font-bold text-slate-900 dark:text-white hover:text-rose-600 transition-colors line-clamp-2">
                            <a href="{{ route('institutions.show', $inst->id) }}">
                                {{ $inst->name }}
                            </a>
                        </h3>

                        @if($inst->address || $inst->city || $inst->state)
                        <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2">
                            {{ $inst->address ?: implode(', ', array_filter([$inst->city, $inst->state, $inst->postcode])) }}
                        </p>
                        @endif
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                        <div class="flex items-center space-x-2 text-slate-500 dark:text-slate-400">
                            @if($inst->phone)
                            <a href="tel:{{ $inst->phone }}" class="hover:text-rose-600" title="{{ $inst->phone }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </a>
                            @endif
                            @if($inst->website)
                            <a href="{{ $inst->website }}" target="_blank" rel="noopener" class="hover:text-rose-600" title="{{ $inst->website }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                            @endif
                        </div>

                        <a href="{{ route('institutions.show', $inst->id) }}" class="text-rose-600 font-bold hover:underline">
                            View Details
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pt-3">
                {{ $institutions->appends(request()->all())->links() }}
            </div>
            @else
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center text-slate-500">
                <p class="text-base font-semibold text-slate-700 dark:text-slate-300">No institutions match your search or filter.</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Try entering a location query above to discover new educational records.</p>
            </div>
            @endif
        </div>
    </div>
</x-layouts.app>