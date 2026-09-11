<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="h-full bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? $title . ' - Alumni Scraper' : 'Alumni & Institution Scraper' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS (CDN fallback + Vite) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fff1f2',
                            100: '#ffe4e6',
                            500: '#f43f5e',
                            600: '#e11d48',
                            700: '#be123c',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Leaflet CSS & JS for OpenStreetMap -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
</head>

<body class="flex flex-col min-h-full font-sans antialiased">
    <!-- Navbar -->
    <nav
        class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}"
                        class="flex items-center gap-2 font-bold text-xl text-slate-900 dark:text-white">
                        <!-- <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-rose-600 to-indigo-600 flex items-center justify-center text-white font-black text-sm">
                            AS
                        </div> -->
                        <span>AlumniScraper</span>
                    </a>
                </div>

                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard') }}"
                        class="text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-rose-600 dark:hover:text-rose-400 transition-colors">
                        Dashboard
                    </a>
                    <a href="{{ route('institutions.index') }}"
                        class="text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-rose-600 dark:hover:text-rose-400 transition-colors">
                        Institutions
                    </a>
                    <a href="{{ route('rfps-platform.index') }}"
                        class="text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-rose-600 dark:hover:text-rose-400 transition-colors">
                        RFPs Platform
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Toast Notifications Container -->
    <div class="fixed top-5 right-5 z-50 flex flex-col gap-3 max-w-sm w-full px-4 sm:px-0 pointer-events-none">
        @if (session('success'))
            <div id="success-alert"
                class="pointer-events-auto p-4 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-emerald-500/30 text-emerald-900 dark:text-emerald-100 shadow-xl shadow-emerald-500/10 flex items-center justify-between gap-3 text-sm transition-all duration-500 ease-in-out transform translate-x-0 opacity-100 scale-100">
                <div class="flex items-center gap-3">
                    <div
                        class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950/80 flex items-center justify-center shrink-0 text-emerald-600 dark:text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="dismissToast('success-alert')"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div id="error-alert"
                class="pointer-events-auto p-4 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-rose-500/30 text-rose-900 dark:text-rose-100 shadow-xl shadow-rose-500/10 flex items-center justify-between gap-3 text-sm transition-all duration-500 ease-in-out transform translate-x-0 opacity-100 scale-100">
                <div class="flex items-center gap-3">
                    <div
                        class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-950/80 flex items-center justify-center shrink-0 text-rose-600 dark:text-rose-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="dismissToast('error-alert')"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="mt-auto bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-4 w-full">
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-start justify-between text-xs text-slate-500 dark:text-slate-400 gap-4">
            <p>&copy; {{ date('Y') }} Alumni Scraper. Powered by OpenStreetMap &amp; Overpass API.</p>
            <!-- <div class="flex items-center gap-4">
                <a href="/api/institutions" target="_blank" class="hover:underline">API Endpoint</a>
                <a href="{{ route('institutions.index') }}" class="hover:underline">Institutions Search</a>
            </div> -->
        </div>
    </footer>
    <script>
        function dismissToast(id) {
            const toast = typeof id === 'string' ? document.getElementById(id) : id;
            if (!toast) return;
            toast.classList.add('opacity-0', 'translate-x-full', 'scale-95');
            setTimeout(() => {
                toast.remove();
            }, 500);
        }

        document.addEventListener('DOMContentLoaded', () => {
            ['success-alert', 'error-alert'].forEach(id => {
                const toast = document.getElementById(id);
                if (toast) {
                    setTimeout(() => dismissToast(toast), 3000);
                }
            });
        });
    </script>
</body>

</html>
