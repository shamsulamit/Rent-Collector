<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand">Portfolio</p>
            <h1 class="mt-1 text-3xl font-extrabold tracking-tight">Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Live collections, occupancy and utility performance.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <select wire:model.live="month" class="input max-w-[140px] bg-white/80 dark:bg-ink-800">
                @foreach (collect(range(0, 11))->map(fn ($i) => now()->subMonths($i)->format('Y-m')) as $m)
                    <option value="{{ $m }}">{{ \Carbon\Carbon::createFromFormat('Y-m', $m)->format('M Y') }}</option>
                @endforeach
            </select>
            <select wire:model.live="propertyId" class="input max-w-[200px] bg-white/80 dark:bg-ink-800">
                <option value="">All properties</option>
                @foreach ($properties as $property)
                    <option value="{{ $property->id }}">{{ $property->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @php
        $rate = ($stats['expected_rent'] ?? 0) > 0 ? round(($stats['collected'] / max(1, $stats['expected_rent'])) * 100) : 0;
    @endphp

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand to-emerald-700 p-5 text-white shadow-lg shadow-brand/25">
            <p class="text-sm text-white/80">Collected</p>
            <p class="mt-2 text-3xl font-extrabold tabular-nums">৳{{ number_format($stats['collected'], 0) }}</p>
            <p class="mt-2 text-xs text-white/70">{{ $rate }}% of expected rent</p>
            <div class="pointer-events-none absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
        </div>
        <div class="stat-card">
            <p class="text-sm text-slate-500 dark:text-slate-400">Expected Rent</p>
            <p class="mt-2 text-2xl font-bold tabular-nums">৳{{ number_format($stats['expected_rent'], 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-slate-500 dark:text-slate-400">Outstanding</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-orange-600 dark:text-orange-400">৳{{ number_format($stats['due'], 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-slate-500 dark:text-slate-400">Net Income</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-sky-600 dark:text-sky-400">৳{{ number_format($stats['net_income'], 2) }}</p>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="stat-card">
            <p class="text-xs text-slate-500">Utilities billed</p>
            <p class="mt-1 text-lg font-bold">৳{{ number_format($stats['utility_billed'], 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-xs text-slate-500">Expenses</p>
            <p class="mt-1 text-lg font-bold text-rose-600">৳{{ number_format($stats['expenses'], 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-xs text-slate-500">Occupancy</p>
            <p class="mt-1 text-lg font-bold">{{ $stats['occupancy_rate'] }}% <span class="text-xs font-normal text-slate-400">{{ $stats['occupied_units'] }}/{{ $stats['total_units'] }}</span></p>
        </div>
        <div class="stat-card">
            <p class="text-xs text-slate-500">Lost rent</p>
            <p class="mt-1 text-lg font-bold text-amber-600">৳{{ number_format($stats['lost_rent'], 2) }}</p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="card p-5 xl:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold">Collection trend</h2>
                <span class="text-xs text-slate-400">Last 6 months</span>
            </div>
            <div class="relative mt-4 h-64">
                <canvas id="chart-collection"></canvas>
            </div>
        </div>
        <div class="card p-5">
            <h2 class="font-semibold">Utility mix</h2>
            <div class="relative mt-4 h-64">
                <canvas id="chart-utilities"></canvas>
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="card p-5">
            <h2 class="font-semibold">Income vs expense</h2>
            <div class="relative mt-4 h-56">
                <canvas id="chart-income"></canvas>
            </div>
        </div>
        <div class="card p-5">
            <h2 class="font-semibold">Occupancy</h2>
            <div class="relative mt-4 h-56">
                <canvas id="chart-occupancy"></canvas>
            </div>
        </div>
        <div class="card p-5">
            <h2 class="font-semibold">Attention needed</h2>
            <div class="mt-3 max-h-56 space-y-2 overflow-y-auto">
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
                    <p class="py-8 text-center text-sm text-slate-400">All clear this month.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card mt-6 p-5">
        <h2 class="font-semibold">Quick actions</h2>
        <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($quickActions as $action)
                <a href="{{ $action['route'] }}" class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2.5 text-xs font-medium text-slate-600 transition hover:border-brand hover:text-brand dark:border-ink-700 dark:text-slate-300">
                    {{ $action['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>

@script
<script>
    const charts = {};

    const theme = () => {
        const dark = document.documentElement.classList.contains('dark');
        return {
            grid: dark ? 'rgba(148,163,184,0.12)' : 'rgba(15,23,42,0.06)',
            tick: dark ? '#94a3b8' : '#64748b',
            legend: dark ? '#cbd5e1' : '#334155',
        };
    };

    const destroy = (id) => { charts[id]?.destroy(); charts[id] = null; };

    const draw = (payload) => {
        if (!window.Chart) return;
        const t = theme();
        const trend = payload.trend || [];
        const income = payload.income || [];
        const utilities = payload.utilities || {};
        const occupancy = payload.occupancy || { occupied: 0, vacant: 0 };

        const collectionEl = document.getElementById('chart-collection');
        if (collectionEl) {
            destroy('collection');
            charts.collection = new Chart(collectionEl, {
                type: 'bar',
                data: {
                    labels: trend.map(p => p.month),
                    datasets: [
                        { label: 'Billed', data: trend.map(p => p.billed), backgroundColor: 'rgba(148,163,184,0.35)', borderRadius: 8, maxBarThickness: 28 },
                        { label: 'Collected', data: trend.map(p => p.collected), backgroundColor: '#0d9488', borderRadius: 8, maxBarThickness: 28 },
                    ],
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: t.legend } } }, scales: { x: { ticks: { color: t.tick }, grid: { display: false } }, y: { ticks: { color: t.tick }, grid: { color: t.grid } } } },
            });
        }

        const utilEl = document.getElementById('chart-utilities');
        if (utilEl) {
            destroy('utilities');
            charts.utilities = new Chart(utilEl, {
                type: 'doughnut',
                data: {
                    labels: ['Electricity', 'Gas', 'Water'],
                    datasets: [{ data: [utilities.electricity || 0, utilities.gas || 0, utilities.water || 0], backgroundColor: ['#0d9488', '#f59e0b', '#38bdf8'], borderWidth: 0 }],
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: { position: 'bottom', labels: { color: t.legend } } } },
            });
        }

        const incomeEl = document.getElementById('chart-income');
        if (incomeEl) {
            destroy('income');
            charts.income = new Chart(incomeEl, {
                type: 'line',
                data: {
                    labels: income.map(p => p.month),
                    datasets: [
                        { label: 'Income', data: income.map(p => p.income), borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.15)', fill: true, tension: 0.35 },
                        { label: 'Expense', data: income.map(p => p.expense), borderColor: '#f43f5e', backgroundColor: 'rgba(244,63,94,0.08)', fill: true, tension: 0.35 },
                    ],
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: t.legend } } }, scales: { x: { ticks: { color: t.tick }, grid: { display: false } }, y: { ticks: { color: t.tick }, grid: { color: t.grid } } } },
            });
        }

        const occEl = document.getElementById('chart-occupancy');
        if (occEl) {
            destroy('occupancy');
            charts.occupancy = new Chart(occEl, {
                type: 'doughnut',
                data: {
                    labels: ['Occupied', 'Vacant'],
                    datasets: [{ data: [occupancy.occupied || 0, occupancy.vacant || 0], backgroundColor: ['#0f766e', '#fb923c'], borderWidth: 0 }],
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom', labels: { color: t.legend } } } },
            });
        }
    };

    $wire.on('dashboard-charts', (payload) => draw(payload));
    draw({
        trend: $wire.trend || [],
        income: $wire.incomeExpense || [],
        utilities: $wire.utilityCollections || {},
        occupancy: {
            occupied: ($wire.stats && $wire.stats.occupied_units) || 0,
            vacant: Math.max(0, (($wire.stats && $wire.stats.total_units) || 0) - (($wire.stats && $wire.stats.occupied_units) || 0)),
        },
    });
</script>
@endscript
