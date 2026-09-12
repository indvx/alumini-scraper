<x-layouts.app title="Create Institution">
    <div class="max-w-3xl mx-auto space-y-6">

        <!-- Back Button & Page Title -->
        <div class="flex items-center justify-between">
            <a href="{{ route('institutions.index') }}"
                class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Institutions
            </a>
        </div>

        <div
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-sm">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-5 mb-6">
                <h1 class="text-xl font-black text-slate-900 dark:text-white">Add New Institution</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Manually register a school, college, university, or kindergarten institution.
                </p>
            </div>

            <form action="{{ route('institutions.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Basic Info Section -->
                <div class="space-y-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">
                        Basic Information
                    </h2>

                    <div>
                        <label for="name"
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                            Institution Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            placeholder="e.g. Stanford University"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border @error('name') border-rose-500 @else border-slate-300 dark:border-slate-700 @enderror text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        @error('name')
                            <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="type"
                                class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Type <span class="text-rose-500">*</span>
                            </label>
                            <select id="type" name="type" required
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border @error('type') border-rose-500 @else border-slate-300 dark:border-slate-700 @enderror text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                                <option value="school" {{ old('type') == 'school' ? 'selected' : '' }}>School</option>
                                <option value="university" {{ old('type') == 'university' ? 'selected' : '' }}>University</option>
                                <option value="college" {{ old('type') == 'college' ? 'selected' : '' }}>College</option>
                                <option value="kindergarten" {{ old('type') == 'kindergarten' ? 'selected' : '' }}>Kindergarten</option>
                                <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('type')
                                <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="search_id"
                                class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Associated Location Search
                            </label>
                            <select id="search_id" name="search_id"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                                <option value="">-- None / General --</option>
                                @foreach ($searches as $s)
                                    <option value="{{ $s->id }}" {{ old('search_id') == $s->id ? 'selected' : '' }}>
                                        {{ $s->query }} ({{ $s->display_name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Location Information Section -->
                <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">
                        Location Details
                    </h2>

                    <div>
                        <label for="address"
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                            Street Address
                        </label>
                        <input type="text" id="address" name="address" value="{{ old('address') }}"
                            placeholder="e.g. 450 Serra Mall"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="city"
                                class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                City
                            </label>
                            <input type="text" id="city" name="city" value="{{ old('city') }}"
                                placeholder="e.g. Stanford"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        </div>

                        <div>
                            <label for="state"
                                class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                State / Province
                            </label>
                            <input type="text" id="state" name="state" value="{{ old('state') }}"
                                placeholder="e.g. California"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        </div>

                        <div>
                            <label for="postcode"
                                class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Postcode / ZIP
                            </label>
                            <input type="text" id="postcode" name="postcode" value="{{ old('postcode') }}"
                                placeholder="e.g. 94305"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        </div>
                    </div>
                </div>

                <!-- Contact & Geographical Section -->
                <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">
                        Contact &amp; Coordinates
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="phone"
                                class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Phone Number
                            </label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                                placeholder="e.g. +1 650-723-2300"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        </div>

                        <div>
                            <label for="website"
                                class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Website URL
                            </label>
                            <input type="url" id="website" name="website" value="{{ old('website') }}"
                                placeholder="https://www.stanford.edu"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border @error('website') border-rose-500 @else border-slate-300 dark:border-slate-700 @enderror text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                            @error('website')
                                <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="latitude"
                                class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Latitude
                            </label>
                            <input type="number" step="any" id="latitude" name="latitude" value="{{ old('latitude') }}"
                                placeholder="e.g. 37.4275"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border @error('latitude') border-rose-500 @else border-slate-300 dark:border-slate-700 @enderror text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                            @error('latitude')
                                <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="longitude"
                                class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Longitude
                            </label>
                            <input type="number" step="any" id="longitude" name="longitude" value="{{ old('longitude') }}"
                                placeholder="e.g. -122.1697"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border @error('longitude') border-rose-500 @else border-slate-300 dark:border-slate-700 @enderror text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                            @error('longitude')
                                <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                    <a href="{{ route('institutions.index') }}"
                        class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold shadow-md shadow-rose-600/20 transition-all transform hover:scale-[1.01] active:scale-[0.99]">
                        Save Institution
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
