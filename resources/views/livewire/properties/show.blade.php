<div>
    <nav class="flex items-center gap-2 text-sm">
        <a href="{{ route('properties.index') }}" class="text-slate-500 hover:text-brand dark:text-slate-400">Properties</a>
        <span class="text-slate-300 dark:text-slate-600">/</span>
        <span class="font-medium">{{ $property->name }}</span>
    </nav>

    <div class="mt-2 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">{{ $property->name }}</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ collect([$property->address, $property->city, $property->area, $property->postal_code])->filter()->implode(', ') ?: 'No address' }}
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('properties.floors', $property) }}" class="btn-secondary">Floors</a>
            <a href="{{ route('properties.units', $property) }}" class="btn-primary">Units</a>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="stat-card"><p class="text-sm text-slate-500">Units</p><p class="mt-1 text-2xl font-bold">{{ $property->units->count() }}</p></div>
        <div class="stat-card"><p class="text-sm text-slate-500">Occupied</p><p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $occupied }}</p></div>
        <div class="stat-card"><p class="text-sm text-slate-500">Vacant</p><p class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $vacant }}</p></div>
        <div class="stat-card"><p class="text-sm text-slate-500">Floors</p><p class="mt-1 text-2xl font-bold">{{ $property->floors->count() }}</p></div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="card p-5">
            <h2 class="font-semibold">Floors</h2>
            <div class="mt-3 space-y-2">
                @forelse ($property->floors as $floor)
                    <a href="{{ route('properties.units', ['property' => $property, 'floor' => $floor->id]) }}" class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3 transition-colors hover:border-brand dark:border-ink-700">
                        <div>
                            <p class="font-medium">{{ $floor->name }}</p>
                            <p class="text-xs text-slate-400">{{ $floor->description }}</p>
                        </div>
                        <span class="badge bg-slate-100 text-slate-600 dark:bg-ink-700 dark:text-slate-300">{{ $floor->units_count }} units</span>
                    </a>
                @empty
                    <p class="py-4 text-center text-sm text-slate-400">No floors yet. <a href="{{ route('properties.floors', $property) }}" class="text-brand hover:underline">Add one.</a></p>
                @endforelse
            </div>
        </div>

        <div class="card p-5">
            <h2 class="font-semibold">Recent Units</h2>
            <div class="mt-3 overflow-x-auto">
                <table class="table-base">
                    <thead><tr><th>Unit</th><th>Floor</th><th>Tenant</th><th>Status</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-ink-700">
                        @forelse ($property->units->take(8) as $unit)
                            <tr>
                                <td class="font-medium">{{ $unit->name }}</td>
                                <td>{{ $unit->floor?->name ?? '—' }}</td>
                                <td>{{ $unit->activeTenancy?->tenant?->full_name ?? '—' }}</td>
                                <td><x-status-badge :label="ucfirst($unit->status)" color="{{ $unit->status === 'occupied' ? 'emerald' : ($unit->status === 'vacant' ? 'slate' : 'amber') }}" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-slate-400">No units yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="card p-5">
            <h2 class="font-semibold">Meters</h2>
            <div class="mt-3 overflow-x-auto">
                <table class="table-base">
                    <thead><tr><th>Number</th><th>Unit</th><th>Type</th><th>Status</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-ink-700">
                        @forelse ($property->meters as $meter)
                            <tr>
                                <td class="font-mono text-xs">{{ $meter->meter_number }}</td>
                                <td>{{ $meter->unit?->name }}</td>
                                <td class="capitalize">{{ $meter->meter_type }} / {{ $meter->utility }}</td>
                                <td><x-status-badge :label="ucfirst($meter->status)" color="{{ $meter->status === 'active' ? 'emerald' : 'slate' }}" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-slate-400">No meters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card p-5">
            <h2 class="font-semibold">Recent Tenancies</h2>
            <div class="mt-3 space-y-2">
                @forelse ($property->tenancies->take(8) as $tenancy)
                    <a href="{{ route('tenants.show', $tenancy->tenant) }}" class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3 transition-colors hover:border-brand dark:border-ink-700">
                        <div>
                            <p class="font-medium">{{ $tenancy->tenant?->full_name }}</p>
                            <p class="text-xs text-slate-400">{{ $tenancy->unit?->name }} &middot; moved in {{ $tenancy->move_in_date?->format('M Y') }}</p>
                        </div>
                        <x-status-badge :label="ucfirst($tenancy->status)" color="{{ $tenancy->status === 'active' ? 'emerald' : 'slate' }}" />
                    </a>
                @empty
                    <p class="py-4 text-center text-sm text-slate-400">No tenancies.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
