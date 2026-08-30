<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Overview of your portfolio performance.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <select wire:model.live="month" class="input max-w-[130px]">
                @foreach (collect(range(0, 11))->map(fn ($i) => now()->subMonths($i)->format('Y-m')) as $m)
                    <option value="{{ $m }}">{{ \Carbon\Carbon::createFromFormat('Y-m', $m)->format('M Y') }}</option>
                @endforeach
            </select>
            <select wire:model.live="propertyId" class="input max-w-[200px]">
                <option value="">All properties</option>
                @foreach ($properties as $property)
                    <option value="{{ $property->id }}">{{ $property->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="stat-card" style="animation-delay: 0.05s">
            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-500 dark:text-slate-400">Expected Rent</p>
                <span class="rounded-lg bg-brand/10 p-1.5 text-brand"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
            </div>
            <p class="mt-2 text-2xl font-bold">৳{{ number_format($stats['expected_rent'], 2) }}</p>
        </div>

        <div class="stat-card" style="animation-delay: 0.1s">
            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-500 dark:text-slate-400">Collected</p>
                <span class="rounded-lg bg-emerald-100 p-1.5 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></span>
            </div>
            <p class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">৳{{ number_format($stats['collected'], 2) }}</p>
        </div>

        <div class="stat-card" style="animation-delay: 0.15s">
            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-500 dark:text-slate-400">Due</p>
                <span class="rounded-lg bg-orange-100 p-1.5 text-orange-600 dark:bg-orange-500/15 dark:text-orange-300"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span>
            </div>
            <p class="mt-2 text-2xl font-bold text-orange-600 dark:text-orange-400">৳{{ number_format($stats['due'], 2) }}</p>
        </div>

        <div class="stat-card" style="animation-delay: 0.2s">
            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-500 dark:text-slate-400">Net Income</p>
                <span class="rounded-lg bg-sky-100 p-1.5 text-sky-600 dark:bg-sky-500/15 dark:text-sky-300"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></span>
            </div>
            <p class="mt-2 text-2xl font-bold text-sky-600 dark:text-sky-400">৳{{ number_format($stats['net_income'], 2) }}</p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="stat-card">
            <p class="text-sm text-slate-500 dark:text-slate-400">Utility Collection</p>
            <p class="mt-2 text-xl font-bold">৳{{ number_format($stats['utility_billed'], 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-slate-500 dark:text-slate-400">Expenses</p>
            <p class="mt-2 text-xl font-bold text-rose-600 dark:text-rose-400">৳{{ number_format($stats['expenses'], 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-slate-500 dark:text-slate-400">Occupancy Rate</p>
            <p class="mt-2 text-xl font-bold">{{ $stats['occupancy_rate'] }}% <span class="text-xs font-normal text-slate-400">({{ $stats['occupied_units'] }}/{{ $stats['total_units'] }})</span></p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-slate-500 dark:text-slate-400">Lost Rent (Vacant)</p>
            <p class="mt-2 text-xl font-bold text-amber-600 dark:text-amber-400">৳{{ number_format($stats['lost_rent'], 2) }}</p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="card p-5 lg:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold">Collection Trend</h2>
                <div class="flex gap-4 text-xs text-slate-500">
                    <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-slate-300"></span> Billed</span>
                    <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-brand"></span> Collected</span>
                </div>
            </div>
            <div class="mt-4 flex h-48 items-end gap-2">
                @php $max = max(1, max(array_column($trend, 'billed') ?: [1])); @endphp
                @foreach ($trend as $point)
                    <div class="group relative flex flex-1 flex-col items-center justify-end gap-1">
                        <div class="w-full rounded-t-lg bg-slate-200 transition-all group-hover:bg-slate-300 dark:bg-ink-700" style="height: {{ max(2, $point['billed'] / $max * 100) }}%"></div>
                        <div class="w-full rounded-t-lg bg-brand transition-all group-hover:bg-brand-dark" style="height: {{ max(2, $point['collected'] / $max * 100) }}%"></div>
                        <span class="text-[10px] text-slate-400">{{ $point['month'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card p-5">
            <h2 class="font-semibold">Utility Collections</h2>
            <div class="mt-4 space-y-4">
                @foreach (['electricity' => 'Electricity', 'gas' => 'Gas', 'water' => 'Water'] as $key => $label)
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-300">{{ $label }}</span>
                            <span class="font-semibold">৳{{ number_format($utilityCollections[$key], 2) }}</span>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-ink-700">
                            @php $umax = max(1, max($utilityCollections) ?: 1); @endphp
                            <div class="h-full rounded-full bg-brand" style="width: {{ $utilityCollections[$key] / $umax * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="card p-5">
            <h2 class="font-semibold">Income vs Expense</h2>
            <div class="mt-4 space-y-3">
                @foreach ($incomeExpense as $point)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500 dark:text-slate-400">{{ $point['month'] }}</span>
                        <span class="font-medium text-emerald-600 dark:text-emerald-400">৳{{ number_format($point['income']) }}</span>
                        <span class="font-medium text-rose-500">৳{{ number_format($point['expense']) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card p-5">
            <h2 class="font-semibold">Attention Needed</h2>
            <div class="mt-3 space-y-2">
                @forelse ($attention as $item)
                    <a href="{{ $item['route'] }}" class="flex items-start gap-3 rounded-xl p-2.5 transition-colors hover:bg-slate-50 dark:hover:bg-ink-700">
                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </span>
                        <span>
                            <span class="block text-sm font-medium">{{ $item['title'] }}</span>
                            <span class="block text-xs text-slate-400">{{ $item['detail'] }}</span>
                        </span>
                    </a>
                @empty
                    <p class="py-6 text-center text-sm text-slate-400">All clear. Nothing needs attention.</p>
                @endforelse
            </div>
        </div>

        <div class="card p-5">
            <h2 class="font-semibold">Quick Actions</h2>
            <div class="mt-3 grid grid-cols-2 gap-2">
                @foreach ($quickActions as $action)
                    <a href="{{ $action['route'] }}" class="flex flex-col items-center gap-1.5 rounded-xl border border-slate-200 p-3 text-center text-xs font-medium text-slate-600 transition-all hover:border-brand hover:text-brand dark:border-ink-700 dark:text-slate-300 dark:hover:border-brand">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            @if ($action['icon'] === 'home') <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 21V10m4 11V10m4 11V10m4 11V10m4 11V10M2 10l10-7 10 7"/>
                            @elseif ($action['icon'] === 'building') <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2m-2 0h-2M7 7h6m-6 4h6m-6 4h4"/>
                            @elseif ($action['icon'] === 'user') <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21v-1a6 6 0 0112 0v1"/>
                            @elseif ($action['icon'] === 'key') <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4v-2l5.257-5.257A6 6 0 1121 9z"/>
                            @elseif ($action['icon'] === 'cash') <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2zm3 8h4"/>
                            @elseif ($action['icon'] === 'gauge') <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v4m0 4v4m0 4v3m-6 2h12M6 21V3l6 4 6-4v18"/>
                            @elseif ($action['icon'] === 'receipt') <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            @elseif ($action['icon'] === 'doc') <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            @else <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2H9l-2-2H6a2 2 0 00-2 2z"/>
                            @endif
                        </svg>
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
