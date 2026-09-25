<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="h-full bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? $title . ' - Alumni & Institution Scraper' : 'Alumni & Institution Scraper' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS (CDN fallback + Vite) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="flex flex-col min-h-screen font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100">
    <!-- Navbar -->
    <nav
        class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 sticky top-0 z-50 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand Logo -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}"
                        class="flex items-center gap-2.5 group font-black text-xl text-slate-900 dark:text-white transition-colors">
                        <div
                            class="w-9 h-9 rounded-xl bg-gradient-to-tr from-rose-600 via-rose-500 to-indigo-600 flex items-center justify-center text-white font-black text-xs shadow-md shadow-rose-600/20 group-hover:scale-105 transition-all">
                            AS
                        </div>
                        <span class="tracking-tight">Alumni<span
                                class="text-rose-600 dark:text-rose-500">Scraper</span></span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="flex items-center gap-1 sm:gap-2">
                    <a href="{{ route('home') }}"
                        class="px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold {{ request()->routeIs('home') ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 font-bold' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }} transition-all">
                        Home
                    </a>
                    <a href="{{ route('institutions.index') }}"
                        class="px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold {{ request()->routeIs('institutions.*') ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 font-bold' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }} transition-all">
                        Institutions
                    </a>
                    <a href="{{ route('rfps-platform.index') }}"
                        class="px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold {{ request()->routeIs('rfps-platform.*') ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 font-bold' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }} transition-all">
                        Platform
                    </a>
                    <a href="{{ route('rfps.index') }}"
                        class="px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold {{ request()->routeIs('rfps.*') ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 font-bold' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }} transition-all">
                        RFPs
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Toast Notifications Container -->
    <div class="fixed top-5 right-5 z-50 flex flex-col gap-3 max-w-sm w-full px-4 sm:px-0 pointer-events-none">
        @if (session('success'))
            <div id="success-alert"
                class="pointer-events-auto p-4 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-emerald-500/30 text-emerald-900 dark:text-emerald-100 shadow-xl shadow-emerald-500/10 flex items-center justify-between gap-3 text-xs sm:text-sm transition-all duration-500 ease-in-out transform translate-x-0 opacity-100 scale-100">
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
                class="pointer-events-auto p-4 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-rose-500/30 text-rose-900 dark:text-rose-100 shadow-xl shadow-rose-500/10 flex items-center justify-between gap-3 text-xs sm:text-sm transition-all duration-500 ease-in-out transform translate-x-0 opacity-100 scale-100">
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

    <!-- Main Content Layout -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        {{ $slot }}
    </main>

    <!-- Unified Footer -->
    <footer class="mt-auto bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-6 w-full">
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 dark:text-slate-400 gap-4">
            <div class="flex items-center gap-2">
                <div
                    class="w-5 h-5 rounded-md bg-rose-600 text-white flex items-center justify-center text-[10px] font-black">
                    AS</div>
                <p>&copy; {{ date('Y') }} <strong>AlumniScraper</strong>. Ingest, scrape, and match procurement
                    RFPs.</p>
            </div>
            <div class="flex items-center gap-4 font-semibold text-slate-600 dark:text-slate-300">
                <a href="{{ route('home') }}"
                    class="hover:text-rose-600 dark:hover:text-rose-400 transition-colors">Home</a>
                <a href="{{ route('institutions.index') }}"
                    class="hover:text-rose-600 dark:hover:text-rose-400 transition-colors">Institutions</a>
                <a href="{{ route('rfps.index') }}"
                    class="hover:text-rose-600 dark:hover:text-rose-400 transition-colors">RFPs Directory</a>
                <a href="{{ route('rfps-platform.index') }}"
                    class="hover:text-rose-600 dark:hover:text-rose-400 transition-colors">Platforms</a>
            </div>
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
                    setTimeout(() => dismissToast(toast), 4000);
                }
            });
        });
    </script>
</body>

</html>
