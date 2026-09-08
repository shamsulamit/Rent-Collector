<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Prepaid Recharges</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Prepaid meters are not billed as postpaid invoices.</p>
        </div>
        <button wire:click="openCreate" class="btn-primary">Record Recharge</button>
    </div>

    <div class="mt-4">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search meter / tenant / ref..." class="input max-w-[260px]">
    </div>

    <div class="card mt-4 overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Date</th><th>Meter</th><th>Tenant</th><th>Unit</th><th class="text-right">Amount</th><th class="text-right">Balance</th><th>Ref</th><th></th></tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-ink-700">
                @forelse ($recharges as $row)
                    <tr>
                        <td>{{ $row->recharge_date->format('d M Y') }}</td>
                        <td class="font-mono text-xs">{{ $row->meter?->meter_number }}</td>
                        <td>{{ $row->tenant?->full_name ?? '—' }}</td>
                        <td>{{ $row->unit?->name ?? '—' }}</td>
                        <td class="text-right font-semibold">৳{{ number_format($row->amount, 2) }}</td>
                        <td class="text-right">৳{{ number_format($row->balance_after, 2) }}</td>
                        <td class="text-xs">{{ $row->reference ?: '—' }}</td>
                        <td class="text-right">
                            <button wire:click="openEdit('{{ $row->id }}')" class="btn-ghost px-2 py-1 text-xs">Edit</button>
                            <button wire:click="delete('{{ $row->id }}')" wire:confirm="Delete this recharge?" class="btn-ghost px-2 py-1 text-xs text-rose-600 dark:text-rose-400">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-10 text-center text-slate-400">No prepaid recharges yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $recharges->links() }}</div>

    @if ($showForm)
    <div class="modal-backdrop" wire:click.self="$set('showForm', false)">
        <div class="modal-panel">
            <h3 class="text-lg font-bold">{{ $editingId ? 'Edit Recharge' : 'Record Recharge' }}</h3>
            <form wire:submit="save" class="mt-5 space-y-4">
                <div>
                    <label class="label">Prepaid meter</label>
                    <select wire:model="form.meter_id" class="input" required>
                        <option value="">Select meter</option>
                        @foreach ($meters as $meter)
                            <option value="{{ $meter->id }}">{{ $meter->meter_number }} · {{ $meter->unit?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Date</label>
                        <input type="date" wire:model="form.recharge_date" class="input" required>
                    </div>
                    <div>
                        <label class="label">Amount (৳)</label>
                        <input type="number" step="0.01" wire:model="form.amount" class="input" required>
                    </div>
                </div>
                <div>
                    <label class="label">Reference</label>
                    <input type="text" wire:model="form.reference" class="input">
                </div>
                <div>
                    <label class="label">Provider</label>
                    <input type="text" wire:model="form.provider" class="input" placeholder="Leave blank to use meter provider">
                </div>
                <div>
                    <label class="label">Notes</label>
                    <textarea wire:model="form.notes" rows="2" class="input"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="$set('showForm', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
