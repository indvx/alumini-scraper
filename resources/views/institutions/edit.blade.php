<x-layouts.app title="Edit Institution">
    <div class="max-w-3xl mx-auto space-y-3">

        <!-- Back Button & Page Title -->
        <div class="flex items-center justify-between">
            <a href="{{ route('institutions.show', $institution) }}" class="inline-flex items-center gap-1 text-xs font-bold tracking-wider text-rose-600 dark:text-rose-400 hover:underline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Institution Details
            </a>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-1 sm:p-5 shadow-sm">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-2 mb-2">
                <h1 class="text-xl font-black text-slate-900 dark:text-white">Edit Institution: {{ $institution->name }}</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Update educational institution details, contact info, and geographical coordinates.
                </p>
            </div>

            <form action="{{ route('institutions.update', $institution) }}" method="POST" class="space-y-2">
                @csrf
                @method('PUT')

                <!-- Basic Information -->
                <div class="space-y-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Basic Information</h2>

                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Institution Name <span class="text-rose-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $institution->name) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border @error('name') border-rose-500 @else border-slate-300 dark:border-slate-700 @enderror text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        @error('name')
                            <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="type" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Institution Type <span class="text-rose-500">*</span></label>
                        <select id="type" name="type" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border @error('type') border-rose-500 @else border-slate-300 dark:border-slate-700 @enderror text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                            <option value="school" {{ old('type', $institution->type) === 'school' ? 'selected' : '' }}>School</option>
                            <option value="college" {{ old('type', $institution->type) === 'college' ? 'selected' : '' }}>College</option>
                            <option value="university" {{ old('type', $institution->type) === 'university' ? 'selected' : '' }}>University</option>
                            <option value="kindergarten" {{ old('type', $institution->type) === 'kindergarten' ? 'selected' : '' }}>Kindergarten</option>
                            <option value="other" {{ old('type', $institution->type) === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('type')
                            <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Location Details Section -->
                <div class="space-y-4 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Location Details</h2>

                    <div>
                        <label for="address" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Street Address</label>
                        <input type="text" id="address" name="address" value="{{ old('address', $institution->address) }}"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        @error('address')
                            <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="city" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">City</label>
                            <input type="text" id="city" name="city" value="{{ old('city', $institution->city) }}"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        </div>

                        <div>
                            <label for="state" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">State / Region</label>
                            <input type="text" id="state" name="state" value="{{ old('state', $institution->state) }}"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        </div>

                        <div>
                            <label for="postcode" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Postcode / ZIP</label>
                            <input type="text" id="postcode" name="postcode" value="{{ old('postcode', $institution->postcode) }}"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="latitude" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Latitude</label>
                            <input type="number" step="any" id="latitude" name="latitude" value="{{ old('latitude', $institution->latitude) }}"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        </div>

                        <div>
                            <label for="longitude" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Longitude</label>
                            <input type="number" step="any" id="longitude" name="longitude" value="{{ old('longitude', $institution->longitude) }}"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        </div>
                    </div>
                </div>

                <!-- Contact & Web Section -->
                <div class="space-y-4 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Contact &amp; Website</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="phone" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Phone Number</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone', $institution->phone) }}"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                            @error('phone')
                                <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="website" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Website URL</label>
                            <input type="url" id="website" name="website" value="{{ old('website', $institution->website) }}" placeholder="https://example.edu"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border @error('website') border-rose-500 @else border-slate-300 dark:border-slate-700 @enderror text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                            @error('website')
                                <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                    <a href="{{ route('institutions.show', $institution) }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold shadow-md shadow-rose-600/20 transition-all">
                        Update Institution
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
