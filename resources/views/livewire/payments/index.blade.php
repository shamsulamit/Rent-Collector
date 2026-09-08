<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Payments</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Record and allocate tenant payments.</p>
        </div>
        <button wire:click="openCreate" class="btn-primary">Record Payment</button>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-2">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search tenant / ref..." class="input max-w-[200px]">
        <select wire:model.live="propertyId" class="input max-w-[180px]">
            <option value="">All properties</option>
            @foreach ($properties as $property)
                <option value="{{ $property->id }}">{{ $property->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="method" class="input max-w-[130px]">
            <option value="">All methods</option>
            <option value="cash">Cash</option>
            <option value="bank">Bank</option>
            <option value="bkash">bKash</option>
            <option value="nagad">Nagad</option>
            <option value="rocket">Rocket</option>
        </select>
        <input type="date" wire:model.live="from" class="input max-w-[150px]">
        <input type="date" wire:model.live="to" class="input max-w-[150px]">
    </div>

    <div class="card mt-4 overflow-x-auto">
        <table class="table-base">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Tenant</th>
                    <th>Property</th>
                    <th>Method</th>
                    <th>Date</th>
                    <th class="text-right">Amount</th>
                    <th class="text-right">Unallocated</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-ink-700">
                @forelse ($payments as $payment)
                    <tr>
                        <td class="font-mono text-xs">{{ $payment->reference ?: '—' }}</td>
                        <td><a href="{{ route('tenants.show', $payment->tenant) }}" class="font-medium hover:text-brand">{{ $payment->tenant?->full_name }}</a></td>
                        <td>{{ $payment->property?->name }}</td>
                        <td><span class="badge bg-slate-100 capitalize text-slate-600 dark:bg-ink-700 dark:text-slate-300">{{ $payment->method }}</span></td>
                        <td>{{ $payment->payment_date->format('d M Y') }}</td>
                        <td class="text-right font-semibold tabular-nums">৳{{ number_format($payment->amount, 2) }}</td>
                        <td class="text-right tabular-nums">
                            @if ($payment->unallocated_amount > 0)
                                <span class="font-semibold text-teal-600 dark:text-teal-400">৳{{ number_format($payment->unallocated_amount, 2) }} credit</span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="btn-ghost px-2 py-1 text-xs">Receipt</a>
                                @if ($payment->unallocated_amount > 0 && auth()->user()->can('allocate', $payment))
                                    <button wire:click="reallocate('{{ $payment->id }}')" class="btn-ghost px-2 py-1 text-xs text-brand-dark dark:text-brand-light">Re-allocate</button>
                                @endif
                                @can('delete', $payment)
                                    <button wire:click="delete('{{ $payment->id }}')" wire:confirm="Delete this payment and reverse allocations?" class="btn-ghost px-2 py-1 text-xs text-rose-600 dark:text-rose-400">Delete</button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-10 text-center text-slate-400">No payments recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $payments->links() }}</div>

    @if ($showForm)
    <div class="modal-backdrop" wire:click.self="$set('showForm', false)">
        <div class="modal-panel">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold">Record Payment</h3>
                <button wire:click="$set('showForm', false)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-ink-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="save" class="mt-5 space-y-4">
                <div>
                    <label class="label">Tenant</label>
                    <select wire:model.live="form.tenant_id" class="input" required>
                        <option value="">Select tenant</option>
                        @foreach ($tenants as $tenant)
                            <option value="{{ $tenant->id }}">{{ $tenant->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Amount (৳)</label>
                        <input type="number" wire:model="form.amount" class="input" step="0.01" required>
                    </div>
                    <div>
                        <label class="label">Date</label>
                        <input type="date" wire:model="form.payment_date" class="input" required>
                    </div>
                    <div>
                        <label class="label">Method</label>
                        <select wire:model="form.method" class="input">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                            <option value="rocket">Rocket</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Allocation strategy</label>
                        <select wire:model="strategy" class="input">
                            <option value="oldest-first">Oldest first</option>
                            <option value="current-first">Current first</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="label">Reference</label>
                    <input type="text" wire:model="form.reference" class="input">
                </div>
                <div>
                    <label class="label">Notes</label>
                    <textarea wire:model="form.notes" rows="2" class="input"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="$set('showForm', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Record &amp; Allocate</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
