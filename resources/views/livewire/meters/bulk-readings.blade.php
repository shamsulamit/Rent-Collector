<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Bulk Meter Entry</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">The primary monthly workflow. Enter all readings, then submit.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <select wire:model.live="month" class="input max-w-[150px]">
                @foreach (collect(range(0, 5))->map(fn ($i) => now()->subMonths($i)->format('Y-m')) as $m)
                    <option value="{{ $m }}">{{ \Carbon\Carbon::createFromFormat('Y-m', $m)->format('M Y') }}</option>
                @endforeach
            </select>
            <button wire:click="saveDraft" class="btn-secondary" wire:loading.attr="disabled">
                <span wire:loading.remove>Save Draft</span>
                <span wire:loading>Saving...</span>
            </button>
            <button wire:click="submitAll" wire:confirm="Submit all readings and calculate charges?" class="btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove>Submit &amp; Calculate</span>
                <span wire:loading>Working...</span>
            </button>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-2">
        <select wire:model.live="utility" class="input max-w-[150px]">
            <option value="electricity">Electricity</option>
            <option value="gas">Gas</option>
            <option value="water">Water</option>
        </select>
        <select wire:model.live="propertyId" class="input max-w-[190px]">
            <option value="">All properties</option>
            @foreach ($properties as $property)
                <option value="{{ $property->id }}">{{ $property->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="floorId" class="input max-w-[150px]">
            <option value="">All floors</option>
            @foreach ($floors as $floor)
                <option value="{{ $floor->id }}">{{ $floor->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="unitId" class="input max-w-[150px]">
            <option value="">All units</option>
            @foreach ($units as $unit)
                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
            @endforeach
        </select>
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search unit..." class="input max-w-[160px]">
    </div>

    @if ($missing > 0)
        <div class="mt-4 flex items-center gap-2 rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-700 dark:text-amber-300">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            {{ $missing }} meter(s) still missing readings for this month.
        </div>
    @endif

    {{-- Desktop table --}}
    <div class="card mt-4 hidden overflow-x-auto md:block">
        <table class="table-base">
            <thead>
                <tr>
                    <th>Property</th>
                    <th>Unit</th>
                    <th>Tenant</th>
                    <th>Meter</th>
                    <th class="text-right">Previous</th>
                    <th class="text-right">Current</th>
                    <th class="text-right">Usage</th>
                    @if ($utility === 'electricity')<th class="text-right">Charge</th>@endif
                    <th>Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-ink-700">
                @forelse ($readings as $meterId => $row)
                    @if (! is_array($row)) @continue @endif
                    <tr>
                        <td>{{ $row['meter']->property?->name }}</td>
                        <td class="font-medium">{{ $row['meter']->unit?->name }}</td>
                        <td>{{ $row['meter']->unit?->activeTenancy?->tenant?->full_name ?? '—' }}</td>
                        <td class="font-mono text-xs">{{ $row['meter']->meter_number }}</td>
                        <td class="text-right tabular-nums">{{ number_format($row['previous'], 1) }}</td>
                        <td class="w-28">
                            <input type="number" wire:model.live="readings.{{ $meterId }}.current" step="0.01" placeholder="—"
                                   class="input px-2 py-1 text-right tabular-nums {{ $errors->has('readings.' . $meterId . '.current') ? 'border-rose-400' : '' }}">
                        </td>
                        <td class="text-right font-semibold tabular-nums">
                            @if (is_numeric($row['current']))
                                {{ number_format(max(0, (float) $row['current'] - (float) $row['previous']), 1) }}
                            @else
                                <span class="text-slate-300 dark:text-slate-600">—</span>
                            @endif
                        </td>
                        @if ($utility === 'electricity')
                            <td class="text-right tabular-nums text-brand-dark dark:text-brand-light">
                                @if (is_numeric($row['current']))
                                    ৳{{ number_format($chargeOf($meterId), 2) }}
                                @else
                                    <span class="text-slate-300 dark:text-slate-600">—</span>
                                @endif
                            </td>
                        @endif
                        <td>
                            <x-status-badge :label="ucfirst($row['status'])" color="{{ $row['status'] === 'submitted' ? 'emerald' : ($row['status'] === 'finalized' ? 'indigo' : 'slate') }}" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="py-10 text-center text-slate-400">No active {{ $utility }} meters found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 md:hidden">
        @forelse ($readings as $meterId => $row)
            @if (! is_array($row)) @continue @endif
            <div class="card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-bold">{{ $row['meter']->unit?->name }}</p>
                        <p class="text-xs text-slate-400">{{ $row['meter']->meter_number }} &middot; {{ $row['meter']->property?->name }}</p>
                    </div>
                    <x-status-badge :label="ucfirst($row['status'])" color="{{ $row['status'] === 'submitted' ? 'emerald' : 'slate' }}" />
                </div>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Tenant: {{ $row['meter']->unit?->activeTenancy?->tenant?->full_name ?? '—' }}</p>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <div>
                        <label class="label text-xs">Previous</label>
                        <p class="rounded-lg bg-slate-50 px-3 py-2 text-sm font-medium tabular-nums dark:bg-ink-900">{{ number_format($row['previous'], 1) }}</p>
                    </div>
                    <div>
                        <label class="label text-xs">Current</label>
                        <input type="number" wire:model.live="readings.{{ $meterId }}.current" step="0.01" placeholder="—"
                               class="input px-2 py-1 text-sm tabular-nums {{ $errors->has('readings.' . $meterId . '.current') ? 'border-rose-400' : '' }}">
                    </div>
                </div>
                <div class="mt-3 flex justify-between text-sm">
                    <span class="text-slate-500 dark:text-slate-400">Usage:
                        <strong>{{ is_numeric($row['current']) ? number_format(max(0, (float) $row['current'] - (float) $row['previous']), 1) : '—' }}</strong>
                    </span>
                    @if ($utility === 'electricity')
                        <span class="text-brand-dark dark:text-brand-light">Charge: <strong>৳{{ is_numeric($row['current']) ? number_format($chargeOf($meterId), 2) : '—' }}</strong></span>
                    @endif
                </div>
            </div>
        @empty
            <p class="col-span-full py-10 text-center text-slate-400">No active {{ $utility }} meters found.</p>
        @endforelse
    </div>
</div>
