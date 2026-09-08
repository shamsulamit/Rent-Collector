<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">{{ ucfirst($utility) }} Bills</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Calculated from bulk meter readings.</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="$set('utility', 'gas')" class="{{ $utility === 'gas' ? 'btn-primary' : 'btn-secondary' }}">Gas</button>
            <button wire:click="$set('utility', 'water')" class="{{ $utility === 'water' ? 'btn-primary' : 'btn-secondary' }}">Water</button>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap gap-2">
        <select wire:model.live="month" class="input max-w-[150px]">
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
    </div>

    <div class="card mt-4 overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Meter</th><th>Tenant</th><th>Unit</th><th class="text-right">Usage</th><th class="text-right">Rate</th><th class="text-right">Charge</th><th>Status</th><th></th></tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-ink-700">
                @forelse ($bills as $bill)
                    <tr>
                        <td class="font-mono text-xs">{{ $bill->meter?->meter_number }}</td>
                        <td>{{ $bill->tenant?->full_name ?? '—' }}</td>
                        <td>{{ $bill->unit?->name }}</td>
                        <td class="text-right">{{ number_format($bill->usage, 1) }}</td>
                        <td class="text-right">৳{{ number_format($bill->rate, 2) }}</td>
                        <td class="text-right font-semibold">৳{{ number_format($bill->charge, 2) }}</td>
                        <td><x-status-badge :label="ucfirst($bill->status)" color="{{ $bill->status }}" /></td>
                        <td class="text-right">
                            <button wire:click="delete('{{ $bill->id }}')" wire:confirm="Delete this bill?" class="btn-ghost px-2 py-1 text-xs text-rose-600 dark:text-rose-400">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-10 text-center text-slate-400">No {{ $utility }} bills for this month.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $bills->links() }}</div>
</div>
