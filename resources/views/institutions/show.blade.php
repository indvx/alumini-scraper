<x-layouts.app :title="$institution->name">
    <div class="flex h-full w-full flex-1 flex-col gap-6">

        <!-- Back Button & Actions -->
        <div class="flex items-center justify-between">
            <a href="{{ route('institutions.index') }}"
                class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 hover:underline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Institutions Search
            </a>
            <div class="flex items-center gap-2">
                <a href="{{ route('institutions.edit', $institution) }}"
                    class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold flex items-center gap-2 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Institution
                </a>
                <form action="{{ route('institutions.destroy', $institution) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this institution?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-4 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 text-xs font-semibold flex items-center gap-2 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            <!-- Left 2 Columns: Institution Profile Header & Data -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Profile Card -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-6">
                    <div class="flex items-center justify-between">
                        <span
                            class="px-3 py-1 rounded-md text-xs font-extrabold uppercase tracking-wider 
                            {{ $institution->type == 'university' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : '' }}
                            {{ $institution->type == 'college' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' : '' }}
                            {{ $institution->type == 'school' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' : '' }}
                            {{ $institution->type == 'kindergarten' ? 'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300' : '' }}">
                            {{ $institution->type }}
                        </span>
                        @if ($institution->osm_id)
                            <span
                                class="text-xs text-slate-500 dark:text-slate-400 font-mono bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-md">
                                OSM {{ strtoupper($institution->osm_type ?? 'Node') }} #{{ $institution->osm_id }}
                            </span>
                        @endif
                    </div>

                    <div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white">
                            {{ $institution->name }}
                        </h1>
                        @if ($institution->address)
                            <p class="text-slate-600 dark:text-slate-400 mt-1 text-sm">
                                {{ $institution->address }}
                            </p>
                        @endif
                    </div>

                    <!-- Attributes Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-slate-100 dark:border-slate-800 text-sm">
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700">
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">City / State / Country</span>
                            <span class="text-slate-900 dark:text-white font-medium mt-0.5 block">
                                {{ implode(', ', array_filter([$institution->city, $institution->state, $institution->country, $institution->postcode])) ?: 'N/A' }}
                            </span>
                        </div>

                        <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700">
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Coordinates</span>
                            <span class="text-slate-900 dark:text-white font-mono text-xs mt-0.5 block">
                                @if ($institution->latitude && $institution->longitude)
                                    {{ number_format($institution->latitude, 6) }},
                                    {{ number_format($institution->longitude, 6) }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>

                        <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700">
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Phone</span>
                            @if ($institution->phone)
                                <a href="tel:{{ $institution->phone }}" class="text-rose-600 font-semibold hover:underline mt-0.5 block">
                                    {{ $institution->phone }}
                                </a>
                            @else
                                <span class="text-slate-400 text-xs mt-0.5 block">Not Listed</span>
                            @endif
                        </div>

                        <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700">
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Website</span>
                            @if ($institution->website)
                                <a href="{{ $institution->website }}" target="_blank" rel="noopener"
                                    class="text-rose-600 font-semibold hover:underline mt-0.5 block truncate">
                                    {{ $institution->website }}
                                </a>
                            @else
                                <span class="text-slate-400 text-xs mt-0.5 block">Not Listed</span>
                            @endif
                        </div>
                    </div>

                    <!-- Embedded Search Origin Data -->
                    @if ($institution->search)
                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 block">
                                Search Origin Information
                            </span>
                            <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-sm">
                                <div>
                                    <strong class="text-slate-900 dark:text-white block text-base">{{ $institution->search->query }}</strong>
                                    <span class="text-xs text-slate-500 dark:text-slate-400 block mt-0.5">{{ $institution->search->display_name }}</span>
                                </div>
                                <div class="text-xs text-slate-400 shrink-0 text-left sm:text-right">
                                    <span class="block font-medium text-slate-600 dark:text-slate-300">Found: {{ $institution->search->total_found }}</span>
                                    <span>{{ $institution->search->searched_at ? $institution->search->searched_at->diffForHumans() : '' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

            </div>

            <!-- Right 1 Column: Associated RFP Platforms Relation -->
            <div class="lg:col-span-1 space-y-6">

                <!-- Associated RFP Platforms Card -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <span>RFP Platforms</span>
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300">
                                    {{ $institution->rfpPlatforms->count() }}
                                </span>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                RFP portals used by this institution.
                            </p>
                        </div>
                        <button type="button" onclick="document.getElementById('add-platform-relation-form').classList.toggle('hidden');"
                            class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold flex items-center gap-1 shadow-sm transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Add</span>
                        </button>
                    </div>

                    <!-- Add Relation Form (Collapsible) -->
                    <div id="add-platform-relation-form" class="hidden p-4 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 space-y-3 transition-all">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">
                            Add RFP Platform Relation
                        </h4>
                        <form action="{{ route('institutions.rfp-platforms.store', $institution) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label for="rfps_platform_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Select RFP Platform <span class="text-rose-500">*</span>
                                </label>
                                <select id="rfps_platform_id" name="rfps_platform_id" required
                                    class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                                    <option value="">-- Choose Platform --</option>
                                    @foreach ($allRfpPlatforms as $plat)
                                        <option value="{{ $plat->id }}">{{ $plat->name }} {{ $plat->domain ? "({$plat->domain})" : '' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label for="confidence" class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Confidence (%)
                                    </label>
                                    <input type="number" min="0" max="100" id="confidence" name="confidence" value="98"
                                        class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                                </div>

                                <div>
                                    <label for="rel_status" class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Status
                                    </label>
                                    <select id="rel_status" name="status"
                                        class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                                        <option value="active" selected>Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label for="discovery_method" class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Discovery Method
                                </label>
                                <input type="text" id="discovery_method" name="discovery_method" value="AI + Web Search"
                                    placeholder="e.g. AI + Web Search"
                                    class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                            </div>

                            <div>
                                <label for="source_title" class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Source Title
                                </label>
                                <input type="text" id="source_title" name="source_title" value="University procurement page"
                                    placeholder="e.g. University procurement page"
                                    class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                            </div>

                            <div>
                                <label for="source_url" class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Evidence URL (Source Link)
                                </label>
                                <input type="url" id="source_url" name="source_url" placeholder="https://procurement.example.edu/bids"
                                    class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label for="first_verified_at" class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        First Verified
                                    </label>
                                    <input type="date" id="first_verified_at" name="first_verified_at" value="{{ date('Y-m-d') }}"
                                        class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                                </div>

                                <div>
                                    <label for="last_verified_at" class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Last Verified
                                    </label>
                                    <input type="date" id="last_verified_at" name="last_verified_at" value="{{ date('Y-m-d') }}"
                                        class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                                </div>
                            </div>

                            <div>
                                <label for="notes" class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Notes
                                </label>
                                <textarea id="notes" name="notes" rows="2" placeholder="e.g. Formal bid opportunities are posted through Bonfire."
                                    class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                            </div>

                            <div class="flex justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-700">
                                <button type="button" onclick="document.getElementById('add-platform-relation-form').classList.add('hidden');"
                                    class="px-3 py-1.5 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="px-4 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-sm transition-all">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- List of Attached Platforms -->
                    @if ($institution->rfpPlatforms->count() > 0)
                        <div class="space-y-3">
                            @foreach ($institution->rfpPlatforms as $plat)
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 space-y-2.5">
                                    <div class="flex items-start justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-2">
                                        <div>
                                            <a href="{{ route('rfps-platform.show', $plat) }}" class="text-sm font-bold text-slate-900 dark:text-white hover:text-rose-600 transition-colors">
                                                {{ $plat->name }}
                                            </a>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $institution->name }}</span> uses <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $plat->name }}</span>
                                            </p>
                                        </div>

                                        <form action="{{ route('institutions.rfp-platforms.destroy', [$institution, $plat]) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to remove relationship with {{ addslashes($plat->name) }}?');"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-1 text-slate-400 hover:text-rose-600 transition-colors" title="Remove relation">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>

                                    <div class="flex items-center gap-1.5 flex-wrap text-[10px]">
                                        @if ($plat->pivot->status === 'active')
                                            <span class="px-2 py-0.5 font-bold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                                Active
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 font-bold rounded-full bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                                                {{ ucfirst($plat->pivot->status) }}
                                            </span>
                                        @endif
                                        @if ($plat->pivot->confidence)
                                            <span class="px-2 py-0.5 font-bold rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300">
                                                Confidence: {{ $plat->pivot->confidence }}%
                                            </span>
                                        @endif
                                    </div>

                                    <div class="space-y-1 text-xs">
                                        @if ($plat->pivot->discovery_method)
                                            <div class="flex justify-between">
                                                <span class="text-slate-400">Discovery Method:</span>
                                                <span class="font-medium text-slate-700 dark:text-slate-300">{{ $plat->pivot->discovery_method }}</span>
                                            </div>
                                        @endif

                                        @if ($plat->pivot->source_title)
                                            <div class="flex justify-between">
                                                <span class="text-slate-400">Source:</span>
                                                <span class="font-medium text-slate-700 dark:text-slate-300">{{ $plat->pivot->source_title }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    @if ($plat->pivot->notes)
                                        <div class="text-[11px] bg-white dark:bg-slate-900 p-2 rounded-lg border border-slate-100 dark:border-slate-800 text-slate-600 dark:text-slate-300">
                                            <span class="font-bold block text-[10px] text-slate-400 uppercase tracking-wider">Notes</span>
                                            {{ $plat->pivot->notes }}
                                        </div>
                                    @endif

                                    @if ($plat->pivot->source_url)
                                        <div class="pt-1 text-right">
                                            <a href="{{ $plat->pivot->source_url }}" target="_blank" rel="noopener"
                                                class="px-2.5 py-1 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-300 font-semibold text-[11px] hover:underline inline-flex items-center gap-1">
                                                <span>Visit Evidence</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 text-center text-xs text-slate-400 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-dashed border-slate-200 dark:border-slate-700">
                            No RFP platform linked yet. Click <strong>Add</strong> to attach one.
                        </div>
                    @endif
                </div>

            </div>

        </div>

    </div>
</x-layouts.app>
