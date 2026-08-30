<header class="sticky top-0 z-30 border-b border-slate-200 bg-white/80 backdrop-blur dark:border-ink-700 dark:bg-ink-800/80">
    <div class="flex h-16 items-center gap-3 px-4 sm:px-6">
        <button @click="mobileOpen = !mobileOpen" class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 lg:hidden dark:text-slate-300 dark:hover:bg-ink-700" aria-label="Toggle navigation">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        <h1 class="truncate text-lg font-bold tracking-tight">{{ $title ?? (request()->segment(1) ? ucwords(str_replace('-', ' ', request()->segment(1))) : 'Dashboard') }}</h1>

        <div class="ml-auto flex items-center gap-3">
            <div x-show="offline" x-transition
                 class="hidden items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700 sm:inline-flex dark:bg-amber-500/20 dark:text-amber-300">
                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Offline
            </div>
            <div x-show="syncing" x-transition
                 class="hidden items-center gap-1.5 rounded-full bg-sky-100 px-3 py-1 text-xs font-medium text-sky-700 sm:inline-flex dark:bg-sky-500/20 dark:text-sky-300">
                <svg class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Syncing
            </div>

            <div class="flex items-center gap-3">
                <div class="hidden text-right sm:block">
                    <div class="text-sm font-semibold">{{ auth()->user()->name }}</div>
                    <div class="text-xs capitalize text-slate-400">{{ auth()->user()->roles->first()?->name ?? 'User' }}</div>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-brand to-emerald-700 text-sm font-bold text-white">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile navigation --}}
    <div x-show="mobileOpen" x-transition x-cloak class="border-t border-slate-200 lg:hidden dark:border-ink-700">
        <div class="space-y-1 px-4 py-4">
            <a href="{{ route('dashboard') }}" class="nav-link">Dashboard</a>
            <a href="{{ route('properties.index') }}" class="nav-link">Properties</a>
            <a href="{{ route('tenants.index') }}" class="nav-link">Tenants</a>
            <a href="{{ route('tenancies.index') }}" class="nav-link">Tenancies</a>
            <a href="{{ route('meters.bulk-readings') }}" class="nav-link">Meter Readings</a>
            <a href="{{ route('bills.index') }}" class="nav-link">Bills</a>
            <a href="{{ route('payments.index') }}" class="nav-link">Payments</a>
            <a href="{{ route('expenses.index') }}" class="nav-link">Expenses</a>
            <a href="{{ route('maintenance.index') }}" class="nav-link">Maintenance</a>
            <a href="{{ route('reports.index') }}" class="nav-link">Reports</a>
            @can('manage backups')
                <a href="{{ route('backups.index') }}" class="nav-link">Backups</a>
            @endcan
            @can('manage settings')
                <a href="{{ route('settings.index') }}" class="nav-link">Settings</a>
            @endcan
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link w-full text-rose-500">Log out</button>
            </form>
        </div>
    </div>
</header>
