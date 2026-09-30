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
                class="hidden absolute top-0 left-0 right-0 z-40 p-5 sm:p-6 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-slate-200/80 dark:border-slate-800/80 shadow-2xl space-y-4 transition-all animate-in fade-in slide-in-from-top-2 duration-200">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-3">
                    <div>
                        <h4
                            class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 flex items-center gap-2">
                            Scrape Procurement Opportunities
                        </h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            Launch live scraper tasks against Bonfire, OpenGov, Jaggaer, PlanetBids &amp; custom
                            institutional portals.
                        </p>
                    </div>
                    <button type="button" title="Close scrape section"
                        onclick="document.getElementById('scrape-rfps-form-container').classList.add('hidden');"
                        class="p-1.5 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="scrape-rfps-form" action="{{ route('rfps.scrape') }}" method="POST" class="space-y-4"
                    onsubmit="handleScrapeSubmit(event)">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label
                                class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Select Institution
                            </label>
                            <select name="institution_id" id="scrape_institution_id"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500 transition-all">
                                <option value="all" selected>All Institutions</option>
                                @foreach ($institutions as $inst)
                                    <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Select Platform <span class="text-rose-500">*</span>
                            </label>
                            <select name="platform_name" required
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500 transition-all">
                                <option value="all" selected>All Platforms (Bonfire / OpenGov / etc)</option>
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
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500 transition-all">
                                <option value="open" selected>Open Opportunities</option>
                                <option value="past">Past / Closed Opportunities</option>
                                <option value="all">All Opportunities</option>
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
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500 transition-all">
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                To Date (Optional)
                            </label>
                            <input type="date" name="to_date"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500 transition-all">
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800/80">
                        <span class="text-[11px] text-slate-400 italic hidden sm:inline">
                            💡 Tip: Leave institution &amp; platform as 'All' to run a system-wide scrape.
                        </span>
                        <div class="flex items-center gap-2">
                            {{-- <button type="button" title="Cancel scraping form"
                                onclick="document.getElementById('scrape-rfps-form-container').classList.add('hidden');"
                                class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition-all">
                                Cancel
                            </button> --}}
                            <button type="submit" id="scrape-submit-btn"
                                title="Launch automated scraper for selected institution &amp; platform"
                                class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 active:scale-[0.98] text-white text-xs font-bold shadow-md shadow-rose-600/20 transition-all flex items-center gap-2">
                                <svg id="scrape-spinner" class="w-4 h-4 hidden animate-spin" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <svg id="scrape-icon" class="w-4 h-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <span id="scrape-submit-btn-text">Start Scraping</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- RFP Filters Form Container -->
            <div
                class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm space-y-3.5">
                <!-- Always Visible Keywords Manager Section (Top of Search) -->
                <div id="keywords-drawer" class="space-y-2.5 transition-all">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <h4
                                class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                                Search Keywords
                            </h4>
                        </div>

                        <!-- Add Custom Keyword Input & Unified Select All Toggle -->
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button type="button" id="toggle-all-keywords-btn" onclick="toggleSelectAllKeywords()"
                                title="Select or deselect all keywords"
                                class="px-3 py-1 text-xs font-bold rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors shadow-xs">
                                Select All
                            </button>
                            <input type="text" id="new-keyword-input" placeholder="Add keyword (e.g. CRM)..."
                                onkeydown="if(event.key === 'Enter'){ event.preventDefault(); addCustomKeyword(); }"
                                class="px-2.5 py-1 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500 w-44">
                            <button type="button" id="add-keyword-btn" onclick="addCustomKeyword()"
                                class="px-2.5 py-1 text-xs font-bold rounded-xl bg-rose-600 hover:bg-rose-700 text-white transition-colors shadow-xs">
                                + Add
                            </button>
                        </div>
                    </div>

                    <!-- 3-Row Multi-Row Flex Wrap with Vertical Scrollbar -->
                    <div id="keywords-container"
                        class="flex flex-wrap items-center gap-2 max-h-[110px] overflow-y-auto pr-1 py-1 scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600 max-w-full">
                        <!-- Populated dynamically with selectable tag pills by JavaScript -->
                    </div>
                </div>

                <form id="rfp-search-filter-form" action="{{ route('rfps.index') }}" method="GET"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Search</label>
                        <input type="text" name="search" id="rfp-search-input"
                            value="{{ $filters['search'] ?? '' }}" placeholder="Title, inst name, ref ID..."
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Status</label>
                        <select name="status"
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                            <option value="all" {{ ($filters['status'] ?? 'all') === 'all' ? 'selected' : '' }}>All
                                Statuses</option>
                            <option value="open" {{ ($filters['status'] ?? '') === 'open' ? 'selected' : '' }}>Open
                            </option>
                            <option value="awarded" {{ ($filters['status'] ?? '') === 'awarded' ? 'selected' : '' }}>
                                Awarded</option>
                            <option value="past"
                                {{ in_array($filters['status'] ?? '', ['past', 'closed']) ? 'selected' : '' }}>Past /
                                Closed</option>
                            <option value="cancelled"
                                {{ in_array($filters['status'] ?? '', ['cancelled', 'canceled']) ? 'selected' : '' }}>
                                Cancelled</option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Institution</label>
                        <select name="institution_id"
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                            <option value="all"
                                {{ ($filters['institution_id'] ?? 'all') === 'all' ? 'selected' : '' }}>All
                                Institutions</option>
                            @foreach ($institutions as $inst)
                                <option value="{{ $inst->id }}"
                                    {{ (string) ($filters['institution_id'] ?? '') === (string) $inst->id ? 'selected' : '' }}>
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
                            <option value="all"
                                {{ ($filters['rfps_platform_id'] ?? 'all') === 'all' ? 'selected' : '' }}>All Platforms
                            </option>
                            @foreach ($platforms as $plat)
                                <option value="{{ $plat->id }}"
                                    {{ (string) ($filters['rfps_platform_id'] ?? '') === (string) $plat->id ? 'selected' : '' }}>
                                    {{ $plat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit" title="Apply filters to the RFPs"
                            class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-bold transition-colors shadow-sm">
                            Apply
                        </button>
                        <a href="{{ route('rfps.index') }}" title="Reset all search filters"
                            class="py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-colors">
                            Clear
                        </a>
                    </div>
                    <div id="hidden-keywords-inputs"></div>
                </form>
            </div>
        </div>

        <div
            class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 text-xs text-slate-500 dark:text-slate-400">
            <span class="font-medium">
                Showing page {{ $rfps->currentPage() }} of {{ $rfps->lastPage() }}
            </span>
            <span class="flex items-center gap-1.5">
                Total <strong>{{ $rfps->total() }}</strong> RFP(s)
            </span>
        </div>

        <!-- RFP Items Container -->
        @if ($rfps->isNotEmpty())
            <div class="space-y-3 max-h-[600px] overflow-y-auto pr-1">
                @foreach ($rfps as $rfp)
                    @php
                        $statusLower = strtolower((string) $rfp->status);
                    @endphp
                    <div
                        @if ($rfp->opportunity_url) onclick="if (!event.target.closest('a, button')) window.open('{{ $rfp->opportunity_url }}', '_blank');"
                            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm hover:border-slate-400 dark:hover:border-slate-700 transition-all space-y-3 cursor-pointer group"
                        @else
                            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm hover:border-slate-400 dark:hover:border-slate-700 transition-all space-y-3" @endif>
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
                                    <a href="{{ $rfp->opportunity_url ?: route('rfps.show', $rfp) }}"
                                        @if ($rfp->opportunity_url) target="_blank" rel="noopener noreferrer" @endif
                                        title="View procurement opportunity: {{ addslashes($rfp->title) }}"
                                        class="group-hover:text-rose-600 dark:group-hover:text-rose-400 transition-colors inline-flex items-center gap-1.5 flex-wrap">
                                        <span>{{ $rfp->title }}</span>
                                        @if ($rfp->opportunity_url)
                                            <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 shrink-0 inline group-hover:text-rose-600 dark:group-hover:text-rose-400 transition-colors"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        @endif
                                    </a>
                                </h3>
                            </div>

                            <!-- Related Institution & Platform -->
                            <div class="flex sm:flex-col items-end gap-2 shrink-0">
                                @if ($rfp->institution)
                                    <a href="{{ route('institutions.show', $rfp->institution) }}"
                                        onclick="event.stopPropagation();"
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
                                        onclick="event.stopPropagation();"
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
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>Open: <strong
                                            class="text-slate-700 dark:text-slate-300">{{ $rfp->date_open ? \Carbon\Carbon::parse($rfp->date_open)->format('M d, Y') : 'N/A' }}</strong></span>
                                </span>

                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>
                                        @if ($rfp->date_close && \Carbon\Carbon::parse($rfp->date_close)->isPast())
                                            <span class="text-red-600 dark:text-red-400">Closed: </span>
                                            <strong class="text-slate-700 dark:text-slate-300">
                                                {{ \Carbon\Carbon::parse($rfp->date_close)->format('M d, Y') }}
                                            </strong>
                                        @elseif ($rfp->date_close && $rfp->date_close != $rfp->date_open)
                                            <span>Deadline:</span>
                                            <strong class="text-slate-700 dark:text-slate-300">
                                                {{ \Carbon\Carbon::parse($rfp->date_close)->format('M d, Y') }}
                                            </strong>
                                        @else
                                            <span>N/A</span>
                                        @endif
                                    </span>
                                </span>

                                @if ($rfp->source)
                                    <span
                                        class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px] font-mono text-slate-500 uppercase">
                                        Source: {{ is_object($rfp->source) ? $rfp->source->value : $rfp->source }}
                                    </span>
                                @endif
                            </div>

                            @if ($rfp->opportunity_url)
                                <a href="{{ $rfp->opportunity_url }}" target="_blank" rel="noopener noreferrer"
                                    onclick="event.stopPropagation();"
                                    title="View external opportunity source for {{ addslashes($rfp->title) }}"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all shrink-0 shadow-sm">
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

    <script>
        // Scrape Form Submit Handling (Disables submit button & shows spinner during execution)
        function handleScrapeSubmit(e) {
            const btn = document.getElementById('scrape-submit-btn');
            if (!btn) return;

            btn.disabled = true;
            btn.classList.add('opacity-70', 'cursor-not-allowed', 'pointer-events-none');

            const textEl = document.getElementById('scrape-submit-btn-text');
            if (textEl) textEl.innerText = 'Scraping in Progress...';

            const spinner = document.getElementById('scrape-spinner');
            if (spinner) spinner.classList.remove('hidden');

            const icon = document.getElementById('scrape-icon');
            if (icon) icon.classList.add('hidden');
        }

        // 2. Multi-Select Keywords Selection Manager System
        const DEFAULT_KEYWORDS = [
            'Alumni', 'Engagement', 'Event', 'Campaign', 'Community', 'Network',
            'Alumnae', 'Alumnus', 'Alumna', 'Software', 'Social', 'Digital',
            'Member', 'Membership', 'Mentor', 'Mentorship', 'Career', 'Fundraising',
            'Donor', 'Constituent', 'Giving', 'CRM', 'Salesforce', 'Blackbaud',
            'Raisers Edge', 'Directory', 'Associations', 'Association', 'Program',
            'Platform', 'Technology Modernization', 'System Migration', 'Networking',
            'Advancement', 'Volunteer', 'Reunion', 'Chapter', 'Portal', 'Migration',
            'Replacement', 'Renewal', 'Procurement', 'Solicitation', 'HiveBrite',
            'Almabase', 'PeopleGrove', 'Graduway', 'Gravyty', 'EnterpriseAlumni', 'Toucantech'
        ];

        function getCustomKeywords() {
            try {
                const stored = localStorage.getItem('rfp_custom_keywords');
                return stored ? JSON.parse(stored) : [];
            } catch (e) {
                return [];
            }
        }

        function saveCustomKeywords(keywords) {
            try {
                localStorage.setItem('rfp_custom_keywords', JSON.stringify(keywords));
            } catch (e) {}
        }

        function getAllVisibleKeywords() {
            const customKw = getCustomKeywords();
            let allKeywords = [];

            customKw.forEach(ck => {
                if (!allKeywords.map(k => k.toLowerCase()).includes(ck.toLowerCase())) {
                    allKeywords.push(ck);
                }
            });

            DEFAULT_KEYWORDS.forEach(dk => {
                if (!allKeywords.map(k => k.toLowerCase()).includes(dk.toLowerCase())) {
                    allKeywords.push(dk);
                }
            });

            return allKeywords;
        }

        const serverKw = @json($filters['keywords'] ?? null);

        function getSelectedKeywords() {
            try {
                const stored = localStorage.getItem('rfp_selected_keywords');
                if (stored !== null) {
                    return JSON.parse(stored);
                }
            } catch (e) {}

            if (Array.isArray(serverKw)) {
                return serverKw;
            }

            // Default: select all visible keywords initially if no preference stored
            return getAllVisibleKeywords();
        }

        function saveSelectedKeywords(selected) {
            try {
                localStorage.setItem('rfp_selected_keywords', JSON.stringify(selected));
            } catch (e) {}
        }

        function syncHiddenKeywordsInputs() {
            const container = document.getElementById('hidden-keywords-inputs');
            if (!container) return;

            const selectedKeywords = getSelectedKeywords();
            const hasSelected = selectedKeywords.length > 0;

            let html = '';
            if (hasSelected) {
                html += '<input type="hidden" name="use_keywords" value="1">';
                html += selectedKeywords
                    .map(kw => `<input type="hidden" name="keywords[]" value="${kw.replace(/"/g, '&quot;')}">`)
                    .join('');
            } else {
                html += '<input type="hidden" name="use_keywords" value="0">';
            }

            container.innerHTML = html;
        }

        function toggleKeyword(kw) {
            let selected = getSelectedKeywords();
            const lowerKw = kw.toLowerCase();
            const exists = selected.some(k => k.toLowerCase() === lowerKw);

            if (exists) {
                selected = selected.filter(k => k.toLowerCase() !== lowerKw);
            } else {
                selected.push(kw);
            }

            saveSelectedKeywords(selected);
            syncHiddenKeywordsInputs();
            renderKeywords();
        }

        function toggleSelectAllKeywords() {
            const all = getAllVisibleKeywords();
            const selected = getSelectedKeywords();
            const isAllSelected = all.length > 0 && selected.length === all.length;

            if (isAllSelected) {
                saveSelectedKeywords([]);
            } else {
                saveSelectedKeywords([...all]);
            }
            syncHiddenKeywordsInputs();
            renderKeywords();
        }

        function addCustomKeyword() {
            const input = document.getElementById('new-keyword-input');
            if (!input) return;
            const kw = input.value.trim();
            if (!kw) return;

            // Save to custom keywords
            let customKw = getCustomKeywords();
            customKw = customKw.filter(k => k.toLowerCase() !== kw.toLowerCase());
            customKw.unshift(kw);
            saveCustomKeywords(customKw);

            // Automatically select the newly added custom keyword
            let selected = getSelectedKeywords();
            if (!selected.some(k => k.toLowerCase() === kw.toLowerCase())) {
                selected.unshift(kw);
                saveSelectedKeywords(selected);
            }

            input.value = '';

            syncHiddenKeywordsInputs();
            renderKeywords();

            const container = document.getElementById('keywords-container');
            if (container) {
                container.scrollTop = 0;
            }
        }

        function removeCustomKeyword(kw) {
            const lowerKw = kw.toLowerCase();

            // 1. Remove from custom keywords
            let customKw = getCustomKeywords();
            customKw = customKw.filter(k => k.toLowerCase() !== lowerKw);
            saveCustomKeywords(customKw);

            // 2. Remove from selected keywords
            let selected = getSelectedKeywords();
            selected = selected.filter(k => k.toLowerCase() !== lowerKw);
            saveSelectedKeywords(selected);

            syncHiddenKeywordsInputs();
            renderKeywords();
        }

        function renderKeywords() {
            const container = document.getElementById('keywords-container');
            if (!container) return;

            const selectedKeywords = getSelectedKeywords();
            const customKeywords = getCustomKeywords();
            const allKeywords = getAllVisibleKeywords();

            const toggleAllBtn = document.getElementById('toggle-all-keywords-btn');
            if (toggleAllBtn) {
                const isAllSelected = allKeywords.length > 0 && selectedKeywords.length === allKeywords.length;
                toggleAllBtn.innerText = isAllSelected ? 'Deselect All' : 'Select All';
            }

            let html = '';

            allKeywords.forEach((kw) => {
                const isSelected = selectedKeywords.some(k => k.toLowerCase() === kw.toLowerCase());
                const isCustom = customKeywords.some(ck => ck.toLowerCase() === kw.toLowerCase());

                let removeBtnHtml = '';
                if (isCustom) {
                    removeBtnHtml = `
                        <button type="button" title="Delete custom keyword ${kw}" onclick="event.stopPropagation(); removeCustomKeyword('${kw.replace(/'/g, "\\'")}')" class="pr-2 pl-1 py-1 ${isSelected ? 'text-indigo-200 hover:text-white hover:bg-indigo-700' : 'text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400'} font-black text-xs transition-colors select-none">
                            ✕
                        </button>
                    `;
                }

                if (isSelected) {
                    html += `
                        <div class="inline-flex items-center rounded-xl border bg-indigo-600 hover:bg-indigo-700 text-white text-xs overflow-hidden shrink-0 transition-all font-bold shadow-xs">
                            <button type="button" title="Click to unselect ${kw}" onclick="toggleKeyword('${kw.replace(/'/g, "\\'")}')" class="px-2.5 py-1 select-none flex items-center gap-1.5 hover:bg-indigo-700 transition-colors">
                                <span>${kw}</span>
                            </button>
                            ${removeBtnHtml}
                        </div>
                    `;
                } else {
                    html += `
                        <div class="inline-flex items-center rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 hover:border-indigo-400 text-xs overflow-hidden shrink-0 transition-all font-medium opacity-80 hover:opacity-100">
                            <button type="button" title="Click to select ${kw}" onclick="toggleKeyword('${kw.replace(/'/g, "\\'")}')" class="px-2.5 py-1 select-none hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors flex items-center gap-1">
                                <span class="text-slate-400 dark:text-slate-500">+</span>
                                <span>${kw}</span>
                            </button>
                            ${removeBtnHtml}
                        </div>
                    `;
                }
            });

            if (allKeywords.length === 0) {
                html =
                    '<div class="inline-flex items-center gap-2 text-xs text-slate-400 italic"><span>No keywords available.</span></div>';
            }

            container.innerHTML = html;
        }

        // Always render keywords on page load and sync server defaults on initial load
        document.addEventListener('DOMContentLoaded', () => {
            if (window.location.search.length === 0 && Array.isArray(serverKw)) {
                saveSelectedKeywords(serverKw);
            }
            syncHiddenKeywordsInputs();
            renderKeywords();
        });
    </script>
</x-layouts.app>
