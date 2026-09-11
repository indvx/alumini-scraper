<x-layouts.app title="Edit RFP Platform">
    <div class="max-w-3xl mx-auto space-y-1">

        <!-- Back Button & Page Title -->
        <div class="flex items-center justify-between">
            <a href="{{ route('rfps-platform.show', $platform) }}"
                class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Platform Details
            </a>
        </div>

        <div
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-6 shadow-sm">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-2 mb-2">
                <h1 class="text-xl font-black text-slate-900 dark:text-white">Edit: {{ $platform->name }} Platform
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Update procurement platform configuration, domain links, location, and status.
                </p>
            </div>

            <form action="{{ route('rfps-platform.update', $platform) }}" method="POST" class="space-y-2">
                @csrf
                @method('PUT')

                <!-- Basic Info Section -->
                <div class="space-y-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Basic
                        Information</h2>

                    <div>
                        <label for="name"
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Platform
                            Name <span class="text-rose-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $platform->name) }}"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border @error('name') border-rose-500 @else border-slate-300 dark:border-slate-700 @enderror text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        @error('name')
                            <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="domain"
                                class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Domain</label>
                            <input type="text" id="domain" name="domain"
                                value="{{ old('domain', $platform->domain) }}"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border @error('domain') border-rose-500 @else border-slate-300 dark:border-slate-700 @enderror text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                            @error('domain')
                                <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="url"
                                class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Website
                                URL</label>
                            <input type="url" id="url" name="url" value="{{ old('url', $platform->url) }}"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border @error('url') border-rose-500 @else border-slate-300 dark:border-slate-700 @enderror text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                            @error('url')
                                <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Classification & Coverage Section -->
                <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">
                        Classification &amp; Coverage</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="platform_type"
                                class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Platform
                                Type</label>
                            <input type="text" id="platform_type" name="platform_type"
                                value="{{ old('platform_type', $platform->platform_type) }}"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        </div>

                        <div>
                            <label for="institution_type"
                                class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Institution
                                Type</label>
                            <input type="text" id="institution_type" name="institution_type"
                                value="{{ old('institution_type', $platform->institution_type) }}"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        </div>

                        <div>
                            <label for="coverage"
                                class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Coverage</label>
                            <input type="text" id="coverage" name="coverage"
                                value="{{ old('coverage', $platform->coverage) }}"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        </div>
                    </div>
                </div>

                <!-- Location Details Section -->
                <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Location
                        Details</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="city"
                                class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">City</label>
                            <input type="text" id="city" name="city"
                                value="{{ old('city', $platform->city) }}"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        </div>

                        <div>
                            <label for="state"
                                class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">State
                                / Province</label>
                            <input type="text" id="state" name="state"
                                value="{{ old('state', $platform->state) }}"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        </div>

                        <div>
                            <label for="country"
                                class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Country</label>
                            <input type="text" id="country" name="country"
                                value="{{ old('country', $platform->country) }}"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        </div>
                    </div>
                </div>

                <!-- Access & Status Section -->
                <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Access &amp;
                        Status Settings</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-center">
                        <div>
                            <label for="status"
                                class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Status</label>
                            <select id="status" name="status"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                                <option value="active"
                                    {{ old('status', $platform->status) === 'active' ? 'selected' : '' }}>Active
                                </option>
                                <option value="inactive"
                                    {{ old('status', $platform->status) === 'inactive' ? 'selected' : '' }}>Inactive
                                </option>
                            </select>
                        </div>

                        <div class="flex items-center gap-2 pt-4 sm:pt-6">
                            <input type="checkbox" id="is_public" name="is_public" value="1"
                                {{ old('is_public', $platform->is_public) ? 'checked' : '' }}
                                class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 dark:bg-slate-800 border-slate-300 dark:border-slate-700">
                            <label for="is_public"
                                class="text-xs font-semibold text-slate-700 dark:text-slate-300 select-none">Publicly
                                Accessible</label>
                        </div>

                        <div class="flex items-center gap-2 pt-4 sm:pt-6">
                            <input type="checkbox" id="requires_login" name="requires_login" value="1"
                                {{ old('requires_login', $platform->requires_login) ? 'checked' : '' }}
                                class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 dark:bg-slate-800 border-slate-300 dark:border-slate-700">
                            <label for="requires_login"
                                class="text-xs font-semibold text-slate-700 dark:text-slate-300 select-none">Requires
                                Login / Account</label>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                    <a href="{{ route('rfps-platform.show', $platform) }}"
                        class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold shadow-md shadow-rose-600/20 transition-all">
                        Update Platform
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
