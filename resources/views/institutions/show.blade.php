<x-layouts.app :title="$institution->name">
    <div class="flex h-full w-full flex-1 flex-col gap-3">

        <!-- Back Button & Actions -->
        <div class="flex items-center justify-between">
            <a href="{{ route('institutions.index') }}"
                class="inline-flex items-center gap-1 text-sm font-semibold text-rose-600 hover:underline">
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

        <!-- Main Detail Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">

            <!-- Left 2 Columns: Profile & Map -->
            <div class="lg:col-span-2 space-y-1">

                <!-- Profile Header -->
                <div
                    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-1">
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
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-slate-100 dark:border-slate-800 text-sm">
                        <div
                            class="p-2 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700">
                            <span
                                class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">City
                                / State</span>
                            <span class="text-slate-900 dark:text-white font-medium mt-0.5 block">
                                {{ implode(', ', array_filter([$institution->city, $institution->state, $institution->postcode])) ?: 'N/A' }}
                            </span>
                        </div>

                        <div
                            class="p-2 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700">
                            <span
                                class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Coordinates</span>
                            <span class="text-slate-900 dark:text-white font-mono text-xs mt-0.5 block">
                                @if ($institution->latitude && $institution->longitude)
                                    {{ number_format($institution->latitude, 6) }},
                                    {{ number_format($institution->longitude, 6) }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>

                        <div
                            class="p-2 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700">
                            <span
                                class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Phone</span>
                            @if ($institution->phone)
                                <a href="tel:{{ $institution->phone }}"
                                    class="text-rose-600 font-semibold hover:underline mt-0.5 block">
                                    {{ $institution->phone }}
                                </a>
                            @else
                                <span class="text-slate-400 text-xs mt-0.5 block">Not Listed</span>
                            @endif
                        </div>

                        <div
                            class="p-2 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700">
                            <span
                                class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Website</span>
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
                </div>

                <!-- Leaflet Map -->
                @if ($institution->latitude && $institution->longitude)
                    <div
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-3">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Map Location</h3>
                        <div id="single-institution-map"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 z-10"
                            style="min-height: 340px; height: 340px;"></div>
                    </div>
                @endif

            </div>

            <!-- Right 1 Column: Location Search & Nearby -->
            <div class="space-y-1">

                <!-- Discovery Origin -->
                @if ($institution->search)
                    <div
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-1 text-sm">
                        <span class="text-xs font-bold uppercase tracking-wider text-rose-600 block">Search
                            Origin</span>
                        <strong
                            class="text-slate-900 dark:text-white block text-base">{{ $institution->search->query }}</strong>
                        <span
                            class="text-xs text-slate-500 dark:text-slate-400 block">{{ $institution->search->display_name }}</span>
                        <div
                            class="pt-1 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-400 flex justify-between">
                            <span>Found: {{ $institution->search->total_found }}</span>
                            <span>{{ $institution->search->searched_at ? $institution->search->searched_at->diffForHumans() : '' }}</span>
                        </div>
                    </div>
                @endif

                <!-- Nearby Institutions -->
                @if (count($nearbyInstitutions) > 0)
                    <div
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-1">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Nearby Institutions</h3>
                        <div class="space-y-1">
                            @foreach ($nearbyInstitutions as $nearby)
                                <a href="{{ route('institutions.show', $nearby->id) }}"
                                    class="block p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-100 dark:border-slate-700 space-y-1 transition-all">
                                    <div class="flex items-center justify-between">
                                        <h4
                                            class="text-sm font-bold text-slate-900 dark:text-white truncate max-w-[180px]">
                                            {{ $nearby->name }}</h4>
                                        <span
                                            class="text-[10px] font-bold text-slate-600 dark:text-slate-300 bg-slate-200 dark:bg-slate-700 px-2 py-0.5 rounded-md">
                                            {{ number_format($nearby->distance, 1) }} km
                                        </span>
                                    </div>
                                    <span
                                        class="text-xs text-slate-500 dark:text-slate-400 block capitalize">{{ $nearby->type }}
                                        &bull; {{ $nearby->city ?: $nearby->state }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

        </div>

    </div>

    @if ($institution->latitude && $institution->longitude)
        <script>
            (function() {
                function runSingleMapInit() {
                    const mapContainer = document.getElementById('single-institution-map');
                    if (!mapContainer) return;

                    if (typeof L === 'undefined') {
                        setTimeout(runSingleMapInit, 100);
                        return;
                    }

                    const lat = parseFloat(@js($institution->latitude));
                    const lon = parseFloat(@js($institution->longitude));

                    if (isNaN(lat) || isNaN(lon)) return;

                    const map = L.map('single-institution-map').setView([lat, lon], 15);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                        maxZoom: 19
                    }).addTo(map);

                    if (L.Icon && L.Icon.Default && L.Icon.Default.prototype._getIconUrl) {
                        delete L.Icon.Default.prototype._getIconUrl;
                        L.Icon.Default.mergeOptions({
                            iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
                            iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                        });
                    }

                    const instName = @js($institution->name);
                    const instAddress = @js($institution->address ?? '');

                    L.marker([lat, lon])
                        .addTo(map)
                        .bindPopup(`<strong>${instName}</strong><br>${instAddress}`)
                        .openPopup();

                    setTimeout(function() {
                        map.invalidateSize();
                    }, 250);
                }

                if (document.readyState === 'complete' || document.readyState === 'interactive') {
                    setTimeout(runSingleMapInit, 50);
                } else {
                    document.addEventListener('DOMContentLoaded', runSingleMapInit);
                }
            })();
        </script>
    @endif
</x-layouts.app>
