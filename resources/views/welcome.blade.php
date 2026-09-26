<x-layouts.app title="Home">
    <div class="py-4 sm:py-6 text-center space-y-6 max-w-4xl mx-auto mt-10">
        {{-- <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-900 text-rose-700 dark:text-rose-300 text-xs font-bold uppercase tracking-wider">
            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
            Global Educational &amp; Procurement Intelligence
        </div> --}}

        <h1 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
            Alumni &amp; Educational Institution Scraper
        </h1>

        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 max-w-2xl mx-auto leading-relaxed">
            Ingest, search, filter, and export schools, colleges, universities, and kindergartens globally using
            OpenStreetMap Nominatim &amp; Overpass APIs, integrated with automated RFP procurement platform scrapers.
        </p>

        <!-- Standardized CTA Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-1">
            <a href="{{ route('institutions.index') }}" title="Browse educational institutions directory"
                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-rose-600/20 transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span>Institutions Directory &rarr;</span>
            </a>

            <a href="{{ route('rfps.index') }}" title="Browse matched procurement RFPs directory"
                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-900 dark:bg-slate-800 hover:bg-slate-800 dark:hover:bg-slate-700 text-white font-bold text-xs sm:text-sm shadow-sm transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>RFPs Directory</span>
            </a>
        </div>

        <!-- Highlights Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pt-4 text-left">
            <div
                class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
                <div
                    class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">OSM Geocoding &amp; Discovery</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Search regions, countries, or cities to ingest school, college, and university records into local
                    database cache.
                </p>
            </div>

            <div
                class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
                <div
                    class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">RFP Procurement Scraping</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Automated API scrapers for Bonfire, OpenGov, Jaggaer, and PlanetBids platforms with live matching.
                </p>
            </div>

            <div
                class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
                <div
                    class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Export &amp; Relational Data</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Streamlined CSV export, bulk URL import, and full institution-to-platform relational tracking.
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>
