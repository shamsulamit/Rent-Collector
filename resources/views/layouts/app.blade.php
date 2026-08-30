<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ theme: localStorage.getItem('theme') || 'system', mobileOpen: false, offline: false, syncing: false }"
      x-init="
        $watch('theme', value => { applyTheme(value); localStorage.setItem('theme', value); });
        applyTheme(theme);
        window.addEventListener('online', () => { offline = false; });
        window.addEventListener('offline', () => { offline = true; });
        if (typeof window.LandlordLedger !== 'undefined') {
            window.LandlordLedger.init();
        }
      "
      :class="theme === 'dark' ? 'dark' : (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : '')">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Landlord Ledger - property and landlord management system">
    <meta name="theme-color" content="#0F172A">
    <title>{{ config('app.name', 'Landlord Ledger') }}</title>

    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <script>
        function applyTheme(value) {
            const dark = value === 'dark' || (value === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
        }
        window.applyTheme = applyTheme;
    </script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 dark:bg-ink-900 dark:text-slate-100">
    <div class="flex min-h-screen">
        @auth
        <x-sidebar />

        <div class="flex min-w-0 flex-1 flex-col">
            <x-topbar :offline="false" />

            <main class="flex-1 overflow-x-hidden px-4 py-6 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl space-y-6">
                    @if (session('message'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
                             class="flex items-center gap-2 rounded-xl border border-brand/30 bg-brand/10 px-4 py-3 text-sm text-brand-dark dark:text-brand-light">
                            <span class="h-2 w-2 rounded-full bg-brand"></span>
                            {{ session('message') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" x-transition
                             class="flex items-center gap-2 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-600 dark:text-rose-400">
                            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                            {{ session('error') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-600 dark:text-rose-400">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
                </div>
            </main>

            <footer class="border-t border-slate-200 px-4 py-4 text-center text-xs text-slate-400 dark:border-ink-700 dark:text-slate-500">
                {{ config('app.name') }} &middot; &copy; {{ date('Y') }}
            </footer>
        </div>
        @endauth
    </div>

    <x-theme-toggle />

    @livewireScripts
    @stack('scripts')
</body>
</html>
