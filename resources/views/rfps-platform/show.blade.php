<x-layouts.app title="{{ $platform->name }}">
    <div class="max-w-4xl mx-auto space-y-6">

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

        <!-- Main Detail Card -->
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
                        <p class="text-xs font-mono text-slate-400 dark:text-slate-500 mt-1">{{ $platform->domain }}</p>
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
                    <h3 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Platform
                        Specs</h3>

                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                            <dt class="text-slate-500 dark:text-slate-400 font-medium">Institution Type</dt>
                            <dd class="font-semibold text-slate-900 dark:text-white">
                                {{ $platform->institution_type ?: 'Not Specified' }}</dd>
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
                    <h3 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Location
                        &amp; Activity</h3>

                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                            <dt class="text-slate-500 dark:text-slate-400 font-medium">City</dt>
                            <dd class="font-semibold text-slate-900 dark:text-white">{{ $platform->city ?: 'N/A' }}
                            </dd>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                            <dt class="text-slate-500 dark:text-slate-400 font-medium">State / Province</dt>
                            <dd class="font-semibold text-slate-900 dark:text-white">{{ $platform->state ?: 'N/A' }}
                            </dd>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                            <dt class="text-slate-500 dark:text-slate-400 font-medium">Country</dt>
                            <dd class="font-semibold text-slate-900 dark:text-white">{{ $platform->country ?: 'N/A' }}
                            </dd>
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
                <span>Created {{ $platform->created_at ? $platform->created_at->format('M d, Y H:i') : 'N/A' }}</span>
                <span>Last Updated
                    {{ $platform->updated_at ? $platform->updated_at->format('M d, Y H:i') : 'N/A' }}</span>
            </div>
        </div>
    </div>
</x-layouts.app>
