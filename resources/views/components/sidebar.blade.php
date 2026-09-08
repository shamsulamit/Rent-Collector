<nav class="hidden shrink-0 border-r border-slate-200 bg-white px-4 py-6 lg:flex lg:w-64 lg:flex-col dark:border-ink-700 dark:bg-ink-800">
    <a href="{{ route('dashboard') }}" class="mb-8 flex items-center gap-3 px-2">
        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-brand to-emerald-700 text-white shadow-lg shadow-brand/30">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 21V10m4 11V10m4 11V10m4 11V10m4 11V10M2 10l10-7 10 7"/></svg>
        </div>
        <div>
            <div class="text-base font-bold tracking-tight">Landlord Ledger</div>
            <div class="text-xs text-slate-400">Property Management</div>
        </div>
    </a>

    <div class="space-y-1 text-sm">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 13h6V4H4v9zm10 7h6v-9h-6v9zm-10 0h6v-4H4v4zm10-16v6h6V4h-6z"/></svg>
            Dashboard
        </a>

        <div class="px-3 pt-4 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Properties</div>
        <a href="{{ route('properties.index') }}" class="nav-link {{ request()->routeIs('properties.*') ? 'nav-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2m-2 0h-2M7 7h6m-6 4h6m-6 4h4"/></svg>
            Properties
        </a>
        <a href="{{ route('tenants.index') }}" class="nav-link {{ request()->routeIs('tenants.*') ? 'nav-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21v-1a6 6 0 0112 0v1"/></svg>
            Tenants
        </a>
        <a href="{{ route('tenancies.index') }}" class="nav-link {{ request()->routeIs('tenancies.*') ? 'nav-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4v-2l5.257-5.257A6 6 0 1121 9z"/></svg>
            Tenancies
        </a>

        <div class="px-3 pt-4 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Billing</div>
        <a href="{{ route('bills.index') }}" class="nav-link {{ request()->routeIs('bills.*') ? 'nav-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            Monthly Bills
        </a>
        <a href="{{ route('payments.index') }}" class="nav-link {{ request()->routeIs('payments.*') ? 'nav-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2zm3 8h4"/></svg>
            Payments
        </a>
        <a href="{{ route('electricity.index') }}" class="nav-link {{ request()->routeIs('electricity.*') ? 'nav-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            Electricity
        </a>
        <a href="{{ route('prepaid.index') }}" class="nav-link {{ request()->routeIs('prepaid.*') ? 'nav-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            Prepaid
        </a>
        <a href="{{ route('utilities.index') }}" class="nav-link {{ request()->routeIs('utilities.*') ? 'nav-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m8.66-13.66l-.7.7M4.04 19.96l-.7.7M21 12h-1M4 12H3m16.66 7.66l-.7-.7M4.04 4.04l-.7-.7M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Gas &amp; Water
        </a>
        <a href="{{ route('meters.index') }}" class="nav-link {{ request()->routeIs('meters.*') ? 'nav-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v4m0 4v4m0 4v3m-6 2h12M6 21V3l6 4 6-4v18"/></svg>
            Meters
        </a>

        <div class="px-3 pt-4 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Operations</div>
        <a href="{{ route('expenses.index') }}" class="nav-link {{ request()->routeIs('expenses.*') ? 'nav-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5 2.5a2.121 2.121 0 013 3L9 17H6v-3l3.5-3.5z"/></svg>
            Expenses
        </a>
        <a href="{{ route('vendors.index') }}" class="nav-link {{ request()->routeIs('vendors.*') ? 'nav-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2m-2 0h-2M7 7h6m-6 4h6m-6 4h4"/></svg>
            Vendors
        </a>
        <a href="{{ route('maintenance.index') }}" class="nav-link {{ request()->routeIs('maintenance.*') ? 'nav-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5a7 7 0 016.95 8.03 6 6 0 01-8.92 6.6 7 7 0 01-6.95-8.03A7 7 0 0111 5zm9 14l-3-3"/></svg>
            Maintenance
        </a>
        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'nav-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm10 0v-9a2 2 0 00-2-2h-2a2 2 0 00-2 2v9a2 2 0 002 2h2a2 2 0 002-2z"/></svg>
            Reports
        </a>

        <div class="px-3 pt-4 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">System</div>
        @can('manage backups')
        <a href="{{ route('backups.index') }}" class="nav-link {{ request()->routeIs('backups.*') ? 'nav-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2H9l-2-2H6a2 2 0 00-2 2z"/></svg>
            Backups
        </a>
        @endcan
        @if (auth()->user()->isOwner() || auth()->user()->can('view audit logs'))
        <a href="{{ route('audit.index') }}" class="nav-link {{ request()->routeIs('audit.*') ? 'nav-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Audit Log
        </a>
        @endif
        @can('manage settings')
        <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'nav-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Settings
        </a>
        @endcan
        @can('manage users')
        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'nav-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Users
        </a>
        @endcan

        <form method="POST" action="{{ route('logout') }}" class="pt-2">
            @csrf
            <button type="submit" class="nav-link w-full text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Log out
            </button>
        </form>
    </div>
</nav>
