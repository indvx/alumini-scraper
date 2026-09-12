<x-layouts.app title="RFPs Platforms">
    <div class="flex h-full w-full flex-1 flex-col gap-6">

        <!-- Top Header & Actions -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-1">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white sm:text-3xl">
                    RFPs Platforms
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Manage RFP procurement sources, portals, and institutional platforms.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-1 w-full sm:w-auto">
                <button type="button" onclick="openAiSearchModal()"
                    class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50
                    dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-semibold flex items-center
                    justify-center gap-2 shadow-sm transition-all">
                    <svg class="w-4 h-4 text-amber-300 animate-pulse" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    <span>Add by AI</span>
                </button>
                <a href="{{ route('rfps-platform.export', request()->all()) }}"
                    class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition-all">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Export CSV</span>
                </a>
                <a href="{{ route('rfps-platform.create') }}"
                    class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold flex items-center justify-center gap-2 shadow-md shadow-rose-600/20 transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Add Platform</span>
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-3 shadow-sm">
            <form action="{{ route('rfps-platform.index') }}" method="GET"
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-1">
                <div>
                    <label
                        class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Search</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                        placeholder="Name, domain, city..."
                        class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                </div>

                <div>
                    <label
                        class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Platform
                        Type</label>
                    <select name="platform_type"
                        class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        <option value="all">All Types</option>
                        @foreach ($platformTypes as $type)
                            <option value="{{ $type }}"
                                {{ ($filters['platform_type'] ?? '') == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label
                        class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Country</label>
                    <select name="country"
                        class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        <option value="all">All Countries</option>
                        @foreach ($countries as $c)
                            <option value="{{ $c }}"
                                {{ ($filters['country'] ?? '') == $c ? 'selected' : '' }}>
                                {{ $c }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label
                        class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Status</label>
                    <select name="status"
                        class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        <option value="all" {{ ($filters['status'] ?? '') == 'all' ? 'selected' : '' }}>All
                            Statuses
                        </option>
                        <option value="active" {{ ($filters['status'] ?? '') == 'active' ? 'selected' : '' }}>
                            Active
                        </option>
                        <option value="inactive" {{ ($filters['status'] ?? '') == 'inactive' ? 'selected' : '' }}>
                            Inactive</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="w-full py-2 px-4 rounded-lg bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-sm font-semibold transition-colors">
                        Filter
                    </button>
                    <a href="{{ route('rfps-platform.index') }}"
                        class="py-2 px-4 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-semibold transition-colors">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Results Table -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm">
            @if ($platforms->count() > 0)
                <div class="h-[52vh] overflow-y-auto rounded-t-2xl">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-800 shadow-sm">
                            <tr
                                class="border-b border-slate-200 dark:border-slate-800 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                <th width="5%" class="py-3.5 px-4">ID</th>
                                <th width="20%" class="py-3.5 px-4">Platform Name</th>
                                <th width="10%" class="py-3.5 px-4">Type</th>
                                <th width="10%" class="py-3.5 px-4">Location</th>
                                <th width="10%" class="py-3.5 px-4">Access</th>
                                <th width="20%" class="py-3.5 px-4">Login Required</th>
                                <th width="10%" class="py-3.5 px-4">Status</th>
                                <th width="10%" class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($platforms as $p)
                                <tr class="hover:bg-slate-500/80 dark:hover:bg-slate-900/70 transition-colors">
                                    <td class="py-4 px-4">
                                        <div class="text-sm text-slate-500 dark:text-slate-400">{{ $p->id }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="font-bold max-w-[200px] truncate text-slate-900 dark:text-white">
                                            <a href="{{ route('rfps-platform.show', $p) }}"
                                                class="hover:text-rose-600 transition-colors"
                                                title="{{ $p->name }}">
                                                {{ $p->name }}
                                            </a>
                                        </div>
                                        @if ($p->domain || $p->url)
                                            <div class="text-xs text-slate-400 dark:text-slate-500 font-mono mt-0.5">
                                                @if ($p->url)
                                                    <a href="{{ $p->url }}" target="_blank" rel="noopener"
                                                        class="hover:underline flex items-center gap-1">
                                                        <span>{{ $p->domain ?: $p->url }}</span>
                                                        <svg class="w-3 h-3 text-slate-400" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                        </svg>
                                                    </a>
                                                @else
                                                    <span>{{ $p->domain }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex flex-col gap-1">
                                            <div>
                                                <span
                                                    class="inline-block max-w-[200px] truncate px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 align-middle"
                                                    title="{{ $p->platform_type ?: 'General' }}">
                                                    {{ $p->platform_type ?: 'General' }}
                                                </span>
                                            </div>
                                            @if ($p->institution_type)
                                                @php
                                                    $types = array_filter(
                                                        array_map('trim', explode(',', $p->institution_type)),
                                                    );
                                                @endphp
                                                <div class="flex flex-wrap gap-1 max-w-[240px]">
                                                    @foreach (array_slice($types, 0, 2) as $t)
                                                        <span
                                                            class="px-1.5 py-0.5 text-[10px] font-medium rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                                            {{ $t }}
                                                        </span>
                                                    @endforeach
                                                    @if (count($types) > 2)
                                                        <span
                                                            class="px-1.5 py-0.5 text-[10px] font-medium rounded bg-slate-100 dark:bg-slate-800 text-slate-400 cursor-help"
                                                            title="{{ implode(', ', array_slice($types, 2)) }}">
                                                            +{{ count($types) - 2 }} more
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-slate-600 dark:text-slate-300 text-xs">
                                        {{ implode(', ', array_filter([$p->city, $p->state, $p->country])) ?: 'N/A' }}
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex flex-wrap gap-1">
                                            @if ($p->is_public)
                                                <span
                                                    class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                    Public
                                                </span>
                                            @else
                                                <span
                                                    class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400">
                                                    Private
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 font-bold">
                                        {{ $p->requires_login ? 'Yes' : 'No' }}
                                    </td>
                                    <td class="py-4 px-4">
                                        @if ($p->status === 'active')
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Active
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('rfps-platform.show', $p) }}"
                                                class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                                                title="View Details">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('rfps-platform.edit', $p) }}"
                                                class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                                                title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                            <form action="{{ route('rfps-platform.destroy', $p) }}" method="POST"
                                                onsubmit="return confirm('Are you sure you want to delete {{ addslashes($p->name) }}?');"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg transition-colors"
                                                    title="Delete">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                    {{ $platforms->withQueryString()->links() }}
                </div>
        </div>
    @else
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-slate-400 mx-auto mb-3" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">No RFP Platforms Found</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Try resetting filters or add a new RFP
                platform
                to get started.</p>
            <div class="mt-4">
                <a href="{{ route('rfps-platform.create') }}"
                    class="px-4 py-2 rounded-lg bg-rose-600 text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Platform
                </a>
            </div>
        </div>
        @endif
    </div>
    </div>

    <!-- AI Search Modal -->
    <div id="aiSearchModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
        <div
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 max-w-3xl w-full shadow-2xl relative space-y-6">

            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <div
                        class="p-2 rounded-2xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">Find Platforms by Location (AI)
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Select Country &amp; State to discover or
                            generate local procurement platforms.</p>
                    </div>
                </div>
                <button type="button" onclick="closeAiSearchModal()"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('rfps-platform.ai-search') }}" method="GET" class="space-y-4"
                id="aiSearchForm">
                <!-- Location Fields in One Line -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-start">
                    <!-- Country Field (Mandatory) -->
                    <div class="relative">
                        <label for="aiCountryInput"
                            class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Country <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="aiCountryInput" name="country" required autocomplete="off"
                            placeholder="Select Country..."
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all" />
                        <input type="hidden" id="aiCountryId" name="country_id" />

                        <!-- Suggestions Dropdown -->
                        <div id="countrySuggestions"
                            class="absolute z-30 left-0 right-0 mt-1 max-h-48 overflow-y-auto bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl hidden divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                        </div>
                    </div>

                    <!-- State Field (Mandatory) -->
                    <div id="stateContainer" class="relative">
                        <label for="aiStateInput"
                            class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            State / Province <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="aiStateInput" name="state" required autocomplete="off"
                            placeholder="Select State..."
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all" />
                        <input type="hidden" id="aiStateId" name="state_id" />

                        <!-- Suggestions Dropdown -->
                        <div id="stateSuggestions"
                            class="absolute z-30 left-0 right-0 mt-1 max-h-48 overflow-y-auto bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl hidden divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                        </div>
                    </div>

                    <!-- City Field (Optional) -->
                    <div id="cityContainer" class="relative">
                        <label for="aiCityInput"
                            class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            City <span class="text-slate-400 font-normal lowercase">(optional)</span>
                        </label>
                        <input type="text" id="aiCityInput" name="city" autocomplete="off"
                            placeholder="Select City..."
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all" />
                        <input type="hidden" id="aiCityId" name="city_id" />

                        <!-- Suggestions Dropdown -->
                        <div id="citySuggestions"
                            class="absolute z-30 left-0 right-0 mt-1 max-h-48 overflow-y-auto bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl hidden divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeAiSearchModal()" id="aiSearchCancelBtn"
                        class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold transition-colors">
                        Cancel
                    </button>
                    <button type="submit" id="aiSearchSubmitBtn"
                        class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-sm font-bold shadow-md shadow-purple-600/20 transition-all flex items-center gap-2">
                        <x-loader id="aiSearchSpinner" class="hidden text-white" />
                        <span id="aiSearchSubmitText">Find Platforms</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let aiLocationCascade = null;

        function openAiSearchModal() {
            document.getElementById('aiSearchModal').classList.remove('hidden');
            if (aiLocationCascade) {
                aiLocationCascade.fetchCountries('');
            }
        }

        function closeAiSearchModal() {
            document.getElementById('aiSearchModal').classList.add('hidden');
            if (aiLocationCascade) {
                aiLocationCascade.closeAllSuggestions();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.initLocationCascade === 'function') {
                aiLocationCascade = window.initLocationCascade({
                    countryInputId: 'aiCountryInput',
                    countryIdInputId: 'aiCountryId',
                    countrySuggestionsId: 'countrySuggestions',

                    stateContainerId: 'stateContainer',
                    stateInputId: 'aiStateInput',
                    stateIdInputId: 'aiStateId',
                    stateSuggestionsId: 'stateSuggestions',
                    stateRequired: true,

                    cityContainerId: 'cityContainer',
                    cityInputId: 'aiCityInput',
                    cityIdInputId: 'aiCityId',
                    citySuggestionsId: 'citySuggestions',

                    hoverClass: 'hover:bg-purple-50 dark:hover:bg-slate-700/80',
                    containerId: 'aiSearchModal',
                });
            }

            const aiSearchForm = document.getElementById('aiSearchForm');
            if (aiSearchForm) {
                aiSearchForm.addEventListener('submit', function() {
                    const submitBtn = document.getElementById('aiSearchSubmitBtn');
                    const cancelBtn = document.getElementById('aiSearchCancelBtn');
                    const spinner = document.getElementById('aiSearchSpinner');
                    const submitText = document.getElementById('aiSearchSubmitText');

                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-75', 'cursor-not-allowed', 'pointer-events-none');
                    }
                    if (cancelBtn) {
                        cancelBtn.disabled = true;
                        cancelBtn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                    }
                    if (spinner) {
                        spinner.classList.remove('hidden');
                    }
                    if (submitText) {
                        submitText.textContent = 'Searching with AI...';
                    }
                });
            }
        });

        // Close modal on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeAiSearchModal();
            }
        });
    </script>
</x-layouts.app>
