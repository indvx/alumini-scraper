<x-layouts.app title="{{ $platform->name }}">
    <div class="flex h-full w-full flex-1 flex-col gap-6">

        <!-- Header Navigation & Quick Actions -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <a href="{{ route('rfps-platform.index') }}"
                class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to RFPs Platforms
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ route('rfps-platform.edit', $platform) }}"
                    class="px-4 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-bold inline-flex items-center gap-1.5 shadow-sm transition-all">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Platform
                </a>

                <form action="{{ route('rfps-platform.destroy', $platform) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete {{ addslashes($platform->name) }}?');"
                    class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-4 py-2 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-900 text-rose-700 dark:text-rose-300 hover:bg-rose-100 text-xs font-bold inline-flex items-center gap-1.5 transition-all">
                        <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
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

            <!-- Left 2 Columns: Platform Specs & Details -->
            <div class="lg:col-span-2 space-y-6">
                <div
                    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-sm space-y-6">

                    <!-- Title & Status Header -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-6">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                    {{ $platform->platform_type ?: 'General Platform' }}
                                </span>
                                @if ($platform->status === 'active')
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                        Active
                                    </span>
                                @else
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                        Inactive
                                    </span>
                                @endif
                            </div>
                            <h1 class="text-2xl font-black text-slate-900 dark:text-white">{{ $platform->name }}</h1>
                            @if ($platform->domain)
                                <p class="text-xs font-mono text-slate-400 dark:text-slate-500 mt-1">
                                    {{ $platform->domain }}</p>
                            @endif
                        </div>

                        @if ($platform->url)
                            <div>
                                <a href="{{ $platform->url }}" target="_blank" rel="noopener"
                                    class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white dark:bg-white dark:hover:bg-slate-100 dark:text-slate-900 text-xs font-bold inline-flex items-center gap-2 shadow-sm transition-all">
                                    <span>Visit Platform</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Details Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Information Block -->
                        <div class="space-y-4">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">
                                Platform Specs</h3>

                            <dl class="space-y-3 text-sm">
                                <div
                                    class="flex justify-between items-start py-2 border-b border-slate-100 dark:border-slate-800">
                                    <dt class="text-slate-500 dark:text-slate-400 font-medium">Institution Type</dt>
                                    <dd class="font-semibold text-slate-900 dark:text-white">
                                        @if ($platform->institution_type)
                                            <div class="flex flex-wrap gap-1 justify-end max-w-xs">
                                                @foreach (array_filter(array_map('trim', explode(',', $platform->institution_type))) as $t)
                                                    <span
                                                        class="px-2 py-0.5 text-xs rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-normal">
                                                        {{ $t }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            Not Specified
                                        @endif
                                    </dd>
                                </div>
                                <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                                    <dt class="text-slate-500 dark:text-slate-400 font-medium">Coverage Area</dt>
                                    <dd class="font-semibold text-slate-900 dark:text-white">
                                        {{ $platform->coverage ?: 'Not Specified' }}</dd>
                                </div>
                                <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                                    <dt class="text-slate-500 dark:text-slate-400 font-medium">Public Access</dt>
                                    <dd class="font-semibold text-slate-900 dark:text-white">
                                        {{ $platform->is_public ? 'Yes (Public)' : 'No (Private)' }}</dd>
                                </div>
                                <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                                    <dt class="text-slate-500 dark:text-slate-400 font-medium">Requires Login</dt>
                                    <dd class="font-semibold text-slate-900 dark:text-white">
                                        {{ $platform->requires_login ? 'Yes' : 'No' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Location & Timestamps Block -->
                        <div class="space-y-4">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">
                                Location &amp; Activity</h3>

                            <dl class="space-y-3 text-sm">
                                <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                                    <dt class="text-slate-500 dark:text-slate-400 font-medium">City</dt>
                                    <dd class="font-semibold text-slate-900 dark:text-white">
                                        {{ $platform->city ?: 'N/A' }}</dd>
                                </div>
                                <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                                    <dt class="text-slate-500 dark:text-slate-400 font-medium">State / Province</dt>
                                    <dd class="font-semibold text-slate-900 dark:text-white">
                                        {{ $platform->state ?: 'N/A' }}</dd>
                                </div>
                                <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                                    <dt class="text-slate-500 dark:text-slate-400 font-medium">Country</dt>
                                    <dd class="font-semibold text-slate-900 dark:text-white">
                                        {{ $platform->country ?: 'N/A' }}</dd>
                                </div>
                                <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                                    <dt class="text-slate-500 dark:text-slate-400 font-medium">Last Checked</dt>
                                    <dd class="font-semibold text-slate-900 dark:text-white">
                                        {{ $platform->last_checked ? $platform->last_checked->diffForHumans() : 'Never' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Meta Footer -->
                    <div
                        class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-400">
                        <span>Created
                            {{ $platform->created_at ? $platform->created_at->format('M d, Y H:i') : 'N/A' }}</span>
                        <span>Last Updated
                            {{ $platform->updated_at ? $platform->updated_at->format('M d, Y H:i') : 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Right 1 Column: Associated Institutions Relation Card -->
            <div class="lg:col-span-1 space-y-6">

                <!-- Associated Institutions Card -->
                <div
                    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <span>Institutions</span>
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300">
                                    {{ $platform->institutions->count() }}
                                </span>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Educational institutions using this platform.
                            </p>
                        </div>
                        <button type="button"
                            onclick="document.getElementById('add-institution-relation-form').classList.toggle('hidden');"
                            class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold flex items-center gap-1 shadow-sm transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Add</span>
                        </button>
                    </div>

                    <!-- Add Relation Form (Collapsible) -->
                    <div id="add-institution-relation-form"
                        class="hidden p-4 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 space-y-3 transition-all">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">
                            Add Institution Relation
                        </h4>
                        <form action="{{ route('rfps-platform.institutions.store', $platform) }}" method="POST"
                            class="space-y-3">
                            @csrf
                            <div>
                                <label for="institution_search_input"
                                    class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Select Institution <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative" id="institution_lookup_wrapper">
                                    <input type="hidden" id="institution_id" name="institution_id" required>
                                    <div class="relative flex items-center">
                                        <input type="text" id="institution_search_input"
                                            placeholder="Type or select institution (10 suggested)..."
                                            autocomplete="off" required
                                            class="w-full px-3 py-2 pr-8 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                                        <button type="button" id="institution_clear_btn"
                                            class="hidden absolute right-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>

                                    <div id="institution_dropdown"
                                        class="hidden absolute z-30 left-0 right-0 mt-1 max-h-56 overflow-y-auto rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-xl p-1 space-y-0.5">
                                        <div id="institution_dropdown_header"
                                            class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800">
                                            Institutions
                                        </div>
                                        <div id="institution_dropdown_list" class="space-y-0.5">
                                            @foreach ($allInstitutions as $inst)
                                                <button type="button" data-id="{{ $inst->id }}"
                                                    data-name="{{ $inst->name }}"
                                                    class="institution-opt-btn w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-rose-50 dark:hover:bg-slate-800/80 transition-colors flex items-center justify-between">
                                                    <span
                                                        class="font-medium text-slate-900 dark:text-white">{{ $inst->name }}</span>
                                                    @if ($inst->city || $inst->state || $inst->type)
                                                        <span
                                                            class="text-[10px] text-slate-400 dark:text-slate-500">{{ $inst->city ?: ($inst->state ?: $inst->type) }}</span>
                                                    @endif
                                                </button>
                                            @endforeach
                                        </div>
                                        <div id="institution_dropdown_empty"
                                            class="hidden p-3 text-center text-xs text-slate-500">
                                            No matching institution found.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label for="rel_confidence"
                                        class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Confidence (%)
                                    </label>
                                    <input type="number" min="0" max="100" id="rel_confidence"
                                        name="confidence" value="98"
                                        class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                                </div>

                                <div>
                                    <label for="inst_rel_status"
                                        class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Status
                                    </label>
                                    <select id="inst_rel_status" name="status"
                                        class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                                        <option value="active" selected>Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label for="inst_discovery_method"
                                    class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Discovery Method
                                </label>
                                <input type="text" id="inst_discovery_method" name="discovery_method"
                                    value="AI + Web Search" placeholder="e.g. AI + Web Search"
                                    class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                            </div>

                            <div>
                                <label for="inst_source_title"
                                    class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Source Title
                                </label>
                                <input type="text" id="inst_source_title" name="source_title"
                                    value="University procurement page" placeholder="e.g. University procurement page"
                                    class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                            </div>

                            <div>
                                <label for="inst_source_url"
                                    class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Evidence URL (Source Link)
                                </label>
                                <input type="url" id="inst_source_url" name="source_url"
                                    placeholder="https://procurement.example.edu/bids"
                                    class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label for="inst_first_verified_at"
                                        class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        First Verified
                                    </label>
                                    <input type="date" id="inst_first_verified_at" name="first_verified_at"
                                        value="{{ date('Y-m-d') }}"
                                        class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                                </div>

                                <div>
                                    <label for="inst_last_verified_at"
                                        class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Last Verified
                                    </label>
                                    <input type="date" id="inst_last_verified_at" name="last_verified_at"
                                        value="{{ date('Y-m-d') }}"
                                        class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500">
                                </div>
                            </div>

                            <div>
                                <label for="inst_notes"
                                    class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Notes
                                </label>
                                <textarea id="inst_notes" name="notes" rows="2"
                                    placeholder="e.g. Formal bid opportunities are posted through Bonfire."
                                    class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                            </div>

                            <div class="flex justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-700">
                                <button type="button"
                                    onclick="document.getElementById('add-institution-relation-form').classList.add('hidden');"
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

                    <!-- List of Attached Institutions -->
                    @if ($platform->institutions->count() > 0)
                        <div class="space-y-3">
                            @foreach ($platform->institutions as $inst)
                                <div
                                    class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 space-y-2.5">
                                    <div
                                        class="flex items-start justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-2">
                                        <div>
                                            <a href="{{ route('institutions.show', $inst) }}"
                                                class="text-sm font-bold text-slate-900 dark:text-white hover:text-rose-600 transition-colors">
                                                {{ $inst->name }}
                                            </a>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                <span
                                                    class="font-semibold text-slate-700 dark:text-slate-300">{{ $inst->name }}</span>
                                                uses <span
                                                    class="font-semibold text-slate-700 dark:text-slate-300">{{ $platform->name }}</span>
                                            </p>
                                        </div>

                                        <form
                                            action="{{ route('rfps-platform.institutions.destroy', [$platform, $inst]) }}"
                                            method="POST"
                                            onsubmit="return confirm('Are you sure you want to remove relationship with {{ addslashes($inst->name) }}?');"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-1 text-slate-400 hover:text-rose-600 transition-colors"
                                                title="Remove relation">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>

                                    <div class="flex items-center gap-1.5 flex-wrap text-[10px]">
                                        <span
                                            class="px-2 py-0.5 font-bold uppercase rounded bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                                            {{ $inst->type }}
                                        </span>
                                        @if ($inst->pivot->status === 'active')
                                            <span
                                                class="px-2 py-0.5 font-bold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                                Active
                                            </span>
                                        @else
                                            <span
                                                class="px-2 py-0.5 font-bold rounded-full bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                                                {{ ucfirst($inst->pivot->status) }}
                                            </span>
                                        @endif
                                        @if ($inst->pivot->confidence)
                                            <span
                                                class="px-2 py-0.5 font-bold rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300">
                                                Confidence: {{ $inst->pivot->confidence }}%
                                            </span>
                                        @endif
                                    </div>

                                    <div class="space-y-1 text-xs">
                                        @if ($inst->pivot->discovery_method)
                                            <div class="flex justify-between">
                                                <span class="text-slate-400">Discovery Method:</span>
                                                <span
                                                    class="font-medium text-slate-700 dark:text-slate-300">{{ $inst->pivot->discovery_method }}</span>
                                            </div>
                                        @endif

                                        @if ($inst->pivot->source_title)
                                            <div class="flex justify-between">
                                                <span class="text-slate-400">Source:</span>
                                                <span
                                                    class="font-medium text-slate-700 dark:text-slate-300">{{ $inst->pivot->source_title }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    @if ($inst->pivot->notes)
                                        <div
                                            class="text-[11px] bg-white dark:bg-slate-900 p-2 rounded-lg border border-slate-100 dark:border-slate-800 text-slate-600 dark:text-slate-300">
                                            <span
                                                class="font-bold block text-[10px] text-slate-400 uppercase tracking-wider">Notes</span>
                                            {{ $inst->pivot->notes }}
                                        </div>
                                    @endif

                                    @if ($inst->pivot->source_url)
                                        <div class="pt-1 text-right">
                                            <a href="{{ $inst->pivot->source_url }}" target="_blank" rel="noopener"
                                                class="px-2.5 py-1 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-300 font-semibold text-[11px] hover:underline inline-flex items-center gap-1">
                                                <span>Visit Evidence</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div
                            class="p-4 text-center text-xs text-slate-400 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-dashed border-slate-200 dark:border-slate-700">
                            No educational institution linked yet. Click <strong>Add</strong> to link one.
                        </div>
                    @endif
                </div>

            </div>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('institution_search_input');
            const hiddenInput = document.getElementById('institution_id');
            const clearBtn = document.getElementById('institution_clear_btn');
            const dropdown = document.getElementById('institution_dropdown');
            const dropdownHeader = document.getElementById('institution_dropdown_header');
            const dropdownList = document.getElementById('institution_dropdown_list');
            const dropdownEmpty = document.getElementById('institution_dropdown_empty');
            const wrapper = document.getElementById('institution_lookup_wrapper');

            if (!searchInput) return;

            let debounceTimer = null;
            const initialItems = @json($allInstitutions);

            function escapeHtml(str) {
                return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(
                    /"/g, '&quot;');
            }

            function renderItems(items, headerText) {
                dropdownHeader.textContent = headerText;
                dropdownList.innerHTML = '';

                if (!items || items.length === 0) {
                    dropdownEmpty.classList.remove('hidden');
                    dropdownList.classList.add('hidden');
                    return;
                }

                dropdownEmpty.classList.add('hidden');
                dropdownList.classList.remove('hidden');

                items.forEach(item => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className =
                        'institution-opt-btn w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-rose-50 dark:hover:bg-slate-800/80 transition-colors flex items-center justify-between';
                    const sub = item.city || item.state || item.type || '';
                    btn.innerHTML = `
                        <span class="font-medium text-slate-900 dark:text-white">${escapeHtml(item.name)}</span>
                        ${sub ? `<span class="text-[10px] text-slate-400 dark:text-slate-500">${escapeHtml(sub)}</span>` : ''}
                    `;
                    btn.addEventListener('click', function() {
                        selectItem(item.id, item.name);
                    });
                    dropdownList.appendChild(btn);
                });
            }

            function selectItem(id, name) {
                hiddenInput.value = id;
                searchInput.value = name;
                clearBtn.classList.remove('hidden');
                dropdown.classList.add('hidden');
            }

            function clearSelection() {
                hiddenInput.value = '';
                searchInput.value = '';
                clearBtn.classList.add('hidden');
                renderItems(initialItems, 'Institutions');
            }

            searchInput.addEventListener('focus', function() {
                if (!hiddenInput.value && !searchInput.value.trim()) {
                    renderItems(initialItems, 'Institutions');
                }
                dropdown.classList.remove('hidden');
            });

            searchInput.addEventListener('input', function() {
                const query = searchInput.value.trim();
                hiddenInput.value = '';
                clearBtn.classList.toggle('hidden', query === '');

                if (!query) {
                    renderItems(initialItems, 'Institutions');
                    dropdown.classList.remove('hidden');
                    return;
                }

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetch(
                            `{{ route('institutions.search-api') }}?query=${encodeURIComponent(query)}`
                        )
                        .then(res => res.json())
                        .then(data => {
                            if (data.success && data.data) {
                                renderItems(data.data,
                                    `Search Results for "${query}" (Max 10)`);
                            } else {
                                renderItems([], 'Search Results');
                            }
                            dropdown.classList.remove('hidden');
                        })
                        .catch(err => {
                            console.error('Institution search error:', err);
                        });
                }, 200);
            });

            clearBtn.addEventListener('click', function() {
                clearSelection();
                searchInput.focus();
            });

            document.addEventListener('click', function(e) {
                if (wrapper && !wrapper.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });

            document.querySelectorAll('.institution-opt-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    selectItem(btn.dataset.id, btn.dataset.name);
                });
            });
        });
    </script>
</x-layouts.app>
