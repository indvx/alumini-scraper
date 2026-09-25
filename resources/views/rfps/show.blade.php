<x-layouts.app title="{{ $rfp->title }}">
    <div class="flex h-full w-full flex-1 flex-col gap-6">

        <!-- Header Navigation & Quick Actions -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <a href="{{ route('rfps.index') }}"
                class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to RFPs Directory
            </a>

            @if ($rfp->opportunity_url || $rfp->portal_url)
                <a href="{{ $rfp->opportunity_url ?: $rfp->portal_url }}" target="_blank" rel="noopener"
                    class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold inline-flex items-center gap-2 shadow-sm transition-all">
                    <span>View Official Opportunity</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            @endif
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <div class="lg:col-span-2 space-y-6">
                <div
                    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-sm space-y-6">

                    <!-- Header -->
                    <div class="space-y-3 border-b border-slate-100 dark:border-slate-800 pb-6">
                        <div class="flex items-center gap-2 flex-wrap">
                            @php
                                $statusLower = strtolower((string) $rfp->status);
                            @endphp
                            <span
                                class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider
                                {{ $statusLower === 'open' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300' : '' }}
                                {{ $statusLower === 'awarded' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/80 dark:text-blue-300' : '' }}
                                {{ in_array($statusLower, ['past', 'closed', 'evaluation']) ? 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300' : '' }}
                                {{ in_array($statusLower, ['cancelled', 'canceled']) ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300' : '' }}
                            ">
                                {{ ucfirst($rfp->status) }}
                            </span>

                            @if ($rfp->reference_id)
                                <span
                                    class="text-xs font-mono font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 rounded-md border border-slate-200 dark:border-slate-700">
                                    Ref: {{ $rfp->reference_id }}
                                </span>
                            @endif

                            @if ($rfp->project_id)
                                <span
                                    class="text-xs font-mono font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 rounded-md border border-slate-200 dark:border-slate-700">
                                    Project ID: {{ $rfp->project_id }}
                                </span>
                            @endif
                        </div>

                        <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">
                            {{ $rfp->title }}
                        </h1>

                        @if ($rfp->department)
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span>Department: {{ $rfp->department }}</span>
                            </p>
                        @endif
                    </div>

                    <!-- Description -->
                    @if ($rfp->description)
                        <div class="space-y-2">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">
                                Description</h3>
                            <div
                                class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed space-y-2 whitespace-pre-line">
                                {{ $rfp->description }}
                            </div>
                        </div>
                    @endif

                    <!-- Timeline & Key Dates -->
                    <div class="space-y-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">
                            Opportunity Dates</h3>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div
                                class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                                <dt class="text-slate-400 mb-1">Open Date</dt>
                                <dd class="font-bold text-slate-900 dark:text-white text-sm">
                                    {{ $rfp->date_open ? \Carbon\Carbon::parse($rfp->date_open)->format('F d, Y H:i') : 'N/A' }}
                                </dd>
                            </div>
                            <div
                                class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                                <dt class="text-slate-400 mb-1">Submission Deadline</dt>
                                <dd class="font-bold text-slate-900 dark:text-white text-sm">
                                    {{ $rfp->date_close ? \Carbon\Carbon::parse($rfp->date_close)->format('F d, Y H:i') : 'N/A' }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                </div>
            </div>

            <!-- Right Sidebar: Context Metadata -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Institution Card -->
                @if ($rfp->institution)
                    <div
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">
                            Institution</h3>
                        <div>
                            <a href="{{ route('institutions.show', $rfp->institution) }}"
                                class="text-base font-extrabold text-slate-900 dark:text-white hover:text-rose-600 transition-colors">
                                {{ $rfp->institution->name }}
                            </a>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                {{ implode(', ', array_filter([$rfp->institution->city, $rfp->institution->state, $rfp->institution->country])) }}
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Platform Card -->
                @if ($rfp->platform)
                    <div
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">
                            Procurement Platform</h3>
                        <div>
                            <a href="{{ route('rfps-platform.show', $rfp->platform) }}"
                                class="text-base font-extrabold text-slate-900 dark:text-white hover:text-rose-600 transition-colors">
                                {{ $rfp->platform->name }}
                            </a>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Type: {{ $rfp->platform->platform_type ?: 'General Platform' }}
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Technical Details Card -->
                <div
                    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Metadata
                    </h3>
                    <dl class="space-y-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                            <dt class="text-slate-400">Source</dt>
                            <dd class="font-mono font-semibold text-slate-700 dark:text-slate-300 uppercase">
                                {{ $rfp->source }}</dd>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                            <dt class="text-slate-400">Public Award</dt>
                            <dd class="font-semibold text-slate-700 dark:text-slate-300">
                                {{ $rfp->is_public_award ? 'Yes' : 'No' }}</dd>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                            <dt class="text-slate-400">Created At</dt>
                            <dd class="text-slate-700 dark:text-slate-300">
                                {{ $rfp->created_at ? $rfp->created_at->format('M d, Y') : 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>
