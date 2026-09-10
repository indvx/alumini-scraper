<x-layouts.app title="Home">
    <div class="py-12 sm:py-16 text-center space-y-8 max-w-4xl mx-auto">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 text-xs font-bold border border-rose-200 dark:border-rose-800">
            <span>OpenStreetMap Ingestion Engine</span>
        </div>

        <h1 class="text-4xl sm:text-6xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
            Alumni & Educational Institution <span class="bg-gradient-to-r from-rose-600 to-indigo-600 bg-clip-text text-transparent">Scraper</span>
        </h1>

        <p class="text-lg text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
            Ingest, search, filter, and export schools, colleges, universities, and kindergartens globally using OpenStreetMap Nominatim and Overpass APIs.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
            <a href="{{ route('public.institutions.index') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-base shadow-lg shadow-rose-600/20 transition-all">
                Discover Institutions &rarr;
            </a>
            <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-100 font-semibold text-base border border-slate-300 dark:border-slate-700 shadow-sm transition-all">
                View Dashboard
            </a>
        </div>
    </div>
</x-layouts.app>
