<div>
    <x-flash />
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Electricity Bills</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Separate postpaid monthly bills: readings → tariff slabs → charges → tenant bill.</p>
        </div>
        <button wire:click="openCreate" class="btn-primary">Add electricity bill</button>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-2">
        <select wire:model.live="month" class="input max-w-[160px]">
            <option value="">All months</option>
            @foreach ($months as $m)
                <option value="{{ $m }}">{{ \Carbon\Carbon::createFromFormat('Y-m', $m)->format('M Y') }}</option>
            @endforeach
        </select>
        <select wire:model.live="propertyId" class="input max-w-[190px]">
            <option value="">All properties</option>
            @foreach ($properties as $property)
                <option value="{{ $property->id }}">{{ $property->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="status" class="input max-w-[150px]">
            <option value="">All statuses</option>
            @foreach (['draft','calculated','finalized','paid','partial','due'] as $st)
                <option value="{{ $st }}">{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Tenant or meter..." class="input max-w-[200px]">
    </div>

    <div class="card mt-4 overflow-x-auto">
        <table class="table-base">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Meter</th>
                    <th>Tenant / Unit</th>
                    <th class="text-right">Previous</th>
                    <th class="text-right">Current</th>
                    <th class="text-right">Usage</th>
                    <th class="text-right">Energy</th>
                    <th class="text-right">Total</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-ink-700">
                @forelse ($bills as $bill)
                    <tr>
                        <td class="whitespace-nowrap">{{ \Carbon\Carbon::createFromFormat('Y-m', $bill->billing_month)->format('M Y') }}</td>
                        <td class="font-mono text-xs">{{ $bill->meter?->meter_number }}</td>
                        <td>
                            <div class="font-medium">{{ $bill->tenant?->full_name ?? '—' }}</div>
                            <div class="text-xs text-slate-400">{{ $bill->unit?->name }} · {{ $bill->property?->name }}</div>
                        </td>
                        <td class="text-right tabular-nums">{{ number_format($bill->previous_reading, 1) }}</td>
                        <td class="text-right tabular-nums">{{ number_format($bill->current_reading, 1) }}</td>
                        <td class="text-right font-semibold tabular-nums">{{ number_format($bill->usage, 1) }}</td>
                        <td class="text-right tabular-nums">৳{{ number_format($bill->energy_charge, 2) }}</td>
                        <td class="text-right font-semibold tabular-nums">৳{{ number_format($bill->total, 2) }}</td>
                        <td><x-status-badge :label="ucfirst($bill->status)" color="{{ $bill->status }}" /></td>
                        <td>
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('electricity.show', $bill) }}" class="btn-ghost px-2 py-1 text-xs">View</a>
                                <a href="{{ route('electricity.pdf', $bill) }}" target="_blank" class="btn-ghost px-2 py-1 text-xs">PDF</a>
                                @if (! $bill->isImmutable() || auth()->user()->isOwner())
                                    <button wire:click="openAdjust('{{ $bill->id }}')" class="btn-ghost px-2 py-1 text-xs">Adjust</button>
                                @endif
                                @if ($bill->status === 'calculated')
                                    <button wire:click="finalize('{{ $bill->id }}')" wire:confirm="Finalize this electricity bill?" class="btn-ghost px-2 py-1 text-xs text-indigo-600 dark:text-indigo-400">Finalize</button>
                                @endif
                                @can('delete', $bill)
                                    <button wire:click="delete('{{ $bill->id }}')" wire:confirm="Delete this electricity bill?" class="btn-ghost px-2 py-1 text-xs text-rose-600 dark:text-rose-400">Delete</button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="py-10 text-center text-slate-400">No electricity bills. Submit postpaid meter readings to calculate them.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $bills->links() }}</div>

    @if ($showAdjust)
    <div class="modal-backdrop" wire:click.self="$set('showAdjust', false)">
        <div class="modal-panel">
            <h3 class="text-lg font-bold">Adjust electricity bill</h3>
            <form wire:submit="saveAdjust" class="mt-5 space-y-4">
                <div>
                    <label class="label">Discount (৳)</label>
                    <input type="number" step="0.01" wire:model="adjust.discount" class="input">
                </div>
                <div>
                    <label class="label">Adjustment (৳)</label>
                    <input type="number" step="0.01" wire:model="adjust.adjustment" class="input">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="$set('showAdjust', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if ($showCreate)
    <div class="modal-backdrop" wire:click.self="$set('showCreate', false)">
        <div class="modal-panel">
            <h3 class="text-lg font-bold">Add electricity bill</h3>
            <form wire:submit="saveCreate" class="mt-5 space-y-4">
                <div>
                    <label class="label">Meter</label>
                    <select wire:model="create.meter_id" class="input" required>
                        <option value="">Select meter</option>
                        @foreach ($postpaidMeters as $meter)
                            <option value="{{ $meter->id }}">{{ $meter->meter_number }} · {{ $meter->unit?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Month</label>
                    <input type="month" wire:model="create.billing_month" class="input" required>
                </div>
                <div>
                    <label class="label">Bill amount (৳)</label>
                    <input type="number" step="0.01" min="0" wire:model="create.total" class="input" required>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="$set('showCreate', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
