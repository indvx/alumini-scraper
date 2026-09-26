<x-layouts.app title="Institutions Directory">
    <div class="flex h-full w-full flex-1 flex-col gap-6">

        <!-- Top Header & Actions -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
                    Institutions Directory
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Explore, search, filter, and manage educational institutions globally.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                <button type="button"
                    title="Open OpenStreetMap location scraper to discover educational institutions"
                    onclick="document.getElementById('scrape-institutions-container').classList.toggle('hidden');"
                    class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all shadow-md shadow-rose-600/20 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Scrape Institutions</span>
                </button>
                <a href="{{ route('institutions.export', request()->query()) }}"
                    class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-bold flex items-center justify-center gap-2 shadow-sm transition-all"
                    title="Export currently filtered institutions to CSV file">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Export CSV</span>
                </a>
                <a href="{{ route('institutions.create') }}"
                    title="Create a new educational institution record manually"
                    class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 text-white text-xs font-bold flex items-center justify-center gap-2 shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Add Institution</span>
                </a>
            </div>
        </div>

        <!-- Filter Bar & Scrape Overlay Container -->
        <div class="relative z-30">
            <div id="scrape-institutions-container"
                class="{{ session('searchResult') || session('searchError') ? '' : 'hidden' }} absolute top-0 left-0 right-0 z-30 transition-all">
                <x-location-search-card />
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                <form action="{{ route('institutions.index') }}" method="GET"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    @if (request('search_id'))
                        <input type="hidden" name="search_id" value="{{ request('search_id') }}">
                    @endif
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Search Name / Address</label>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                            placeholder="Name, city, state..."
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Type</label>
                        <select name="type"
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                            <option value="all">All Types</option>
                            <option value="school" {{ ($filters['type'] ?? '') == 'school' ? 'selected' : '' }}>School
                            </option>
                            <option value="university" {{ ($filters['type'] ?? '') == 'university' ? 'selected' : '' }}>
                                University</option>
                            <option value="college" {{ ($filters['type'] ?? '') == 'college' ? 'selected' : '' }}>
                                College</option>
                            <option value="kindergarten"
                                {{ ($filters['type'] ?? '') == 'kindergarten' ? 'selected' : '' }}>Kindergarten
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Postcode</label>
                        <input type="text" name="postcode" value="{{ $filters['postcode'] ?? '' }}"
                            placeholder="ZIP / Postcode..."
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit"
                            title="Search educational institutions"
                            class="w-full bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white font-bold py-2 px-4 rounded-xl text-xs transition-colors shadow-sm">
                            Search
                        </button>
                        <a href="{{ route('institutions.index') }}"
                            title="Reset search criteria"
                            class="py-2 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-colors">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
            <span>Showing page {{ $institutions->currentPage() }} of {{ $institutions->lastPage() }}</span>
            <span>Total {{ $institutions->total() }} record(s)</span>
        </div>

        <!-- Grid of Cards -->
        @if ($institutions->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-[600px] overflow-y-auto pr-1">
                @foreach ($institutions as $inst)
                    <div id="{{ $inst->id }}"
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm hover:border-slate-400 dark:hover:border-slate-700 flex flex-col justify-between space-y-3 transition-all">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider 
                                    {{ $inst->type == 'university' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : '' }}
                                    {{ $inst->type == 'college' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' : '' }}
                                    {{ $inst->type == 'school' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' : '' }}
                                    {{ $inst->type == 'kindergarten' ? 'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300' : '' }}">
                                    {{ $inst->type }}
                                </span>
                                @if ($inst->osm_id)
                                    <span class="text-[10px] text-slate-400 font-mono">OSM #{{ $inst->osm_id }}</span>
                                @endif
                            </div>

                            <h3 class="text-base font-extrabold text-slate-900 dark:text-white hover:text-rose-600 transition-colors line-clamp-2">
                                <a href="{{ route('institutions.show', $inst->id) }}"
                                    title="View details for {{ addslashes($inst->name) }}">
                                    {{ $inst->name }}
                                </a>
                            </h3>

                            @if ($inst->address || $inst->city || $inst->state || $inst->country)
                                <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2 leading-relaxed">
                                    {{ $inst->address ?: implode(', ', array_filter([$inst->city, $inst->state, $inst->country, $inst->postcode])) }}
                                </p>
                            @endif
                        </div>

                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                            <div class="flex items-center space-x-2 text-slate-500 dark:text-slate-400">
                                @if ($inst->phone)
                                    <a href="tel:{{ $inst->phone }}" class="hover:text-rose-600 p-1" title="Call {{ $inst->phone }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                    </a>
                                @endif
                                @if ($inst->website)
                                    <a href="{{ $inst->website }}" target="_blank" rel="noopener" class="hover:text-rose-600 p-1" title="Visit website: {{ $inst->website }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('institutions.edit', $inst->id) }}"
                                    class="p-1 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
                                    title="Edit {{ addslashes($inst->name) }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                <form action="{{ route('institutions.destroy', $inst->id) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this institution?');"
                                    class="inline-flex items-center">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors"
                                        title="Delete {{ addslashes($inst->name) }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                                <a href="{{ route('institutions.show', $inst->id) }}"
                                    title="View full institution profile for {{ addslashes($inst->name) }}"
                                    class="text-xs font-bold text-rose-600 hover:underline">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if ($institutions->hasPages())
                <div class="pt-3">
                    {{ $institutions->appends(request()->all())->links() }}
                </div>
            @endif
        @else
            <div
                class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center text-slate-500">
                <p class="text-base font-semibold text-slate-700 dark:text-slate-300">No institutions match your search or filter.</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Try entering a location query above to discover new educational records.</p>
            </div>
        @endif
    </div>
</x-layouts.app>
