<x-layouts.app title="RFPs Directory">
    <div class="flex h-full w-full flex-1 flex-col gap-6">

        <!-- Top Header & Scrape Button -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
                    Request for Proposals (RFPs)
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Explore matched alumni &amp; institutional procurement opportunities.
                </p>
            </div>

            <button type="button" title="Open form to trigger procurement RFP scraper"
                onclick="document.getElementById('scrape-rfps-form-container').classList.toggle('hidden');"
                class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <span>Scrape RFPs</span>
            </button>
        </div>

        <!-- Filter Bar & Scrape Overlay Container -->
        <div class="relative z-30">
            <div id="scrape-rfps-form-container"
                class="hidden absolute top-0 left-0 right-0 z-30 p-5 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 transition-all">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h4
                        class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Scrape Procurement Opportunities
                    </h4>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-slate-400 hidden sm:inline">Specify institution and platform to
                            trigger scraper</span>
                        <button type="button" title="Close scrape section"
                            onclick="document.getElementById('scrape-rfps-form-container').classList.add('hidden');"
                            class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <form action="{{ route('rfps.scrape') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label
                                class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Select Institution <span class="text-rose-500">*</span>
                            </label>
                            <select name="institution_id" id="scrape_institution_id"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500 transition-all">
                                <option value="all" selected>All</option>
                                @foreach ($institutions as $inst)
                                    <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Or Custom Institution Name
                            </label>
                            <input type="text" name="institution_name" placeholder="e.g. Houston City College"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500 transition-all">
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Select Platform <span class="text-rose-500">*</span>
                            </label>
                            <select name="platform_name" required
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500 transition-all">
                                <option value="all" selected>All</option>
                                <option value="Bonfire">Bonfire</option>
                                <option value="OpenGov">OpenGov</option>
                                <option value="Jaggaer">Jaggaer</option>
                                <option value="PlanetBids">PlanetBids</option>
                                @foreach ($platforms as $plat)
                                    @if (!in_array($plat->name, ['Bonfire', 'OpenGov', 'Jaggaer', 'PlanetBids']))
                                        <option value="{{ $plat->name }}">{{ $plat->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Opportunity Type
                            </label>
                            <select name="type"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500 transition-all">
                                <option value="open" selected>Open Opportunities</option>
                                <option value="past">Past / Closed Opportunities</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                From Date (Optional)
                            </label>
                            <input type="date" name="from_date"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500 transition-all">
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                To Date (Optional)
                            </label>
                            <input type="date" name="to_date"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500 transition-all">
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" title="Cancel scraping form"
                            onclick="document.getElementById('scrape-rfps-form-container').classList.add('hidden');"
                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition-all">
                            Cancel
                        </button>
                        <button type="submit" title="Launch automated scraper for selected institution &amp; platform"
                            class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span>Start Scraping</span>
                        </button>
                    </div>
                </form>
            </div>

            <div
                class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                <form action="{{ route('rfps.index') }}" method="GET"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Search</label>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                            placeholder="Title, description, ref ID..."
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Status</label>
                        <select name="status"
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                            <option value="all" {{ ($filters['status'] ?? 'all') == 'all' ? 'selected' : '' }}>All
                                Statuses</option>
                            <option value="open" {{ ($filters['status'] ?? '') == 'open' ? 'selected' : '' }}>Open
                            </option>
                            <option value="awarded" {{ ($filters['status'] ?? '') == 'awarded' ? 'selected' : '' }}>
                                Awarded</option>
                            <option value="past" {{ ($filters['status'] ?? '') == 'past' ? 'selected' : '' }}>Past /
                                Closed</option>
                            <option value="cancelled"
                                {{ ($filters['status'] ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Institution</label>
                        <select name="institution_id"
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                            <option value="all">All Institutions</option>
                            @foreach ($institutions as $inst)
                                <option value="{{ $inst->id }}"
                                    {{ ($filters['institution_id'] ?? '') == $inst->id ? 'selected' : '' }}>
                                    {{ $inst->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Platform</label>
                        <select name="rfps_platform_id"
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                            <option value="all">All Platforms</option>
                            @foreach ($platforms as $plat)
                                <option value="{{ $plat->id }}"
                                    {{ ($filters['rfps_platform_id'] ?? '') == $plat->id ? 'selected' : '' }}>
                                    {{ $plat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit" title="Search procurement RFPs"
                            class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-bold transition-colors shadow-sm">
                            Search
                        </button>
                        <a href="{{ route('rfps.index') }}" title="Reset all search filters"
                            class="py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-colors">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
            <span>Showing page {{ $rfps->currentPage() }} of {{ $rfps->lastPage() }}</span>
            <span>Total {{ $totalCount }} RFP(s) ({{ $openCount }} open)</span>
        </div>

        <!-- RFP Items Container -->
        @if ($rfps->isNotEmpty())
            <div class="space-y-3 max-h-[600px] overflow-y-auto pr-1">
                @foreach ($rfps as $rfp)
                    @php
                        $statusLower = strtolower((string) $rfp->status);
                    @endphp
                    <div
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm hover:border-slate-400 dark:hover:border-slate-700 transition-all space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                            <div class="space-y-1.5 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <!-- Status Badge -->
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider
                                        {{ $statusLower === 'open' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300' : '' }}
                                        {{ $statusLower === 'awarded' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/80 dark:text-blue-300' : '' }}
                                        {{ in_array($statusLower, ['past', 'closed', 'evaluation']) ? 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300' : '' }}
                                        {{ in_array($statusLower, ['cancelled', 'canceled']) ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300' : '' }}
                                    ">
                                        {{ ucfirst($rfp->status) }}
                                    </span>

                                    <!-- Ref ID / Project ID -->
                                    @if ($rfp->reference_id)
                                        <span
                                            class="text-[10px] font-mono font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-md border border-slate-200 dark:border-slate-700">
                                            Ref: {{ $rfp->reference_id }}
                                        </span>
                                    @elseif ($rfp->project_id)
                                        <span
                                            class="text-[10px] font-mono font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-md border border-slate-200 dark:border-slate-700">
                                            ID: {{ $rfp->project_id }}
                                        </span>
                                    @endif

                                    <!-- Department -->
                                    @if ($rfp->department)
                                        <span
                                            class="text-[11px] font-medium text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            <span>{{ $rfp->department }}</span>
                                        </span>
                                    @endif
                                </div>

                                <!-- Title -->
                                <h3
                                    class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white leading-snug">
                                    <a href="{{ route('rfps.show', $rfp) }}"
                                        title="View RFP procurement details for {{ addslashes($rfp->title) }}"
                                        class="hover:text-rose-600 dark:hover:text-rose-400 transition-colors">
                                        {{ $rfp->title }}
                                    </a>
                                </h3>
                            </div>

                            <!-- Related Institution & Platform -->
                            <div class="flex sm:flex-col items-end gap-2 shrink-0">
                                @if ($rfp->institution)
                                    <a href="{{ route('institutions.show', $rfp->institution) }}"
                                        title="View profile for {{ addslashes($rfp->institution->name) }}"
                                        class="px-3 py-1 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold flex items-center gap-1.5 transition-colors">
                                        <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <span>{{ $rfp->institution->name }}</span>
                                    </a>
                                @endif

                                @if ($rfp->platform)
                                    <a href="{{ route('rfps-platform.show', $rfp->platform) }}"
                                        title="View platform details for {{ addslashes($rfp->platform->name) }}"
                                        class="px-3 py-1 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 text-rose-700 dark:text-rose-300 text-xs font-bold flex items-center gap-1.5 transition-colors">
                                        <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" />
                                        </svg>
                                        <span>{{ $rfp->platform->name }}</span>
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Description -->
                        @if ($rfp->description)
                            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed line-clamp-3">
                                {{ strip_tags($rfp->description) }}
                            </p>
                        @endif

                        <!-- Footer Details: Dates & Link -->
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-500 dark:text-slate-400">
                            <div class="flex items-center gap-4 flex-wrap">
                                @if ($rfp->date_open)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>Open: <strong
                                                class="text-slate-700 dark:text-slate-300">{{ \Carbon\Carbon::parse($rfp->date_open)->format('M d, Y') }}</strong></span>
                                    </span>
                                @endif

                                @if ($rfp->date_close)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Deadline: <strong
                                                class="text-slate-700 dark:text-slate-300">{{ \Carbon\Carbon::parse($rfp->date_close)->format('M d, Y') }}</strong></span>
                                    </span>
                                @endif

                                @if ($rfp->source)
                                    <span
                                        class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px] font-mono text-slate-500 uppercase">
                                        Source: {{ is_object($rfp->source) ? $rfp->source->value : $rfp->source }}
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('rfps.show', $rfp) }}"
                                    title="View full procurement details for {{ addslashes($rfp->title) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all">
                                    <span>Details</span>
                                </a>
                                @if ($rfp->opportunity_url || $rfp->portal_url)
                                    <a href="{{ $rfp->opportunity_url ?: $rfp->portal_url }}" target="_blank"
                                        rel="noopener" title="Open official opportunity page in a new window"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition-all w-fit">
                                        <span>View Opportunity</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if ($rfps->hasPages())
                <div class="pt-1">
                    {{ $rfps->links() }}
                </div>
            @endif
        @else
            <div
                class="p-12 text-center bg-white dark:bg-slate-900 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 space-y-3">
                <svg class="w-12 h-12 mx-auto text-slate-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">No RFPs Found</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                    There are no RFPs matching your filters. Try resetting search parameters or scraping platform
                    opportunities.
                </p>
                <a href="{{ route('rfps.index') }}"
                    class="inline-block px-4 py-2.5 rounded-xl bg-slate-900 dark:bg-slate-700 text-white text-xs font-bold">
                    Clear Filters
                </a>
            </div>
        @endif

    </div>
</x-layouts.app>
