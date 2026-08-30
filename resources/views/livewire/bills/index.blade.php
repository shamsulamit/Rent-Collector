<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Monthly Bills</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Generate, finalize and track tenant bills.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button wire:click="generateBills" class="btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove>Generate Monthly Bills</span>
                <span wire:loading>Generating...</span>
            </button>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-2">
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
        <select wire:model.live="status" class="input max-w-[140px]">
            <option value="">All statuses</option>
            <option value="draft">Draft</option>
            <option value="finalized">Finalized</option>
            <option value="paid">Paid</option>
            <option value="partial">Partial</option>
            <option value="due">Due</option>
            <option value="overpaid">Overpaid</option>
        </select>
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search tenant / bill no..." class="input max-w-[220px]">
    </div>

    <div class="card mt-4 overflow-x-auto">
        <table class="table-base">
            <thead>
                <tr>
                    <th>Bill No</th>
                    <th>Tenant</th>
                    <th>Unit</th>
                    <th>Property</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Paid</th>
                    <th class="text-right">Balance</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-ink-700">
                @forelse ($bills as $bill)
                    <tr>
                        <td class="font-mono text-xs">{{ $bill->bill_no }}</td>
                        <td><a href="{{ route('tenants.show', $bill->tenant) }}" class="font-medium hover:text-brand">{{ $bill->tenant?->full_name }}</a></td>
                        <td>{{ $bill->unit?->name }}</td>
                        <td>{{ $bill->property?->name }}</td>
                        <td class="text-right font-semibold tabular-nums">৳{{ number_format($bill->total, 2) }}</td>
                        <td class="text-right text-emerald-600 tabular-nums dark:text-emerald-400">৳{{ number_format($bill->paid, 2) }}</td>
                        <td class="text-right font-semibold tabular-nums {{ $bill->balance() < 0 ? 'text-teal-600 dark:text-teal-400' : ($bill->balance() > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400') }}">
                            ৳{{ number_format($bill->balance(), 2) }}
                        </td>
                        <td><x-status-badge :label="ucfirst($bill->status)" color="{{ $bill->status }}" /></td>
                        <td>
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('bills.pdf', $bill) }}" target="_blank" title="PDF" class="btn-ghost px-2 py-1 text-xs">PDF</a>
                                @if ($bill->status === 'draft' && auth()->user()->can('finalize', $bill))
                                    <button wire:click="finalize('{{ $bill->id }}')" wire:confirm="Finalize this bill? It becomes immutable." class="btn-ghost px-2 py-1 text-xs text-indigo-600 dark:text-indigo-400">Finalize</button>
                                @endif
                                @if (in_array($bill->status, ['finalized', 'due', 'partial']) && $bill->balance() > 0 && auth()->user()->can('recordPayment', $bill))
                                    <button wire:click="openPayment('{{ $bill->id }}')" class="btn-ghost px-2 py-1 text-xs text-brand-dark dark:text-brand-light">Record Payment</button>
                                @endif
                                <button wire:click="sendWhatsApp('{{ $bill->id }}', 'monthly_bill')" class="btn-ghost px-2 py-1 text-xs text-emerald-600 dark:text-emerald-400">WhatsApp</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="py-10 text-center text-slate-400">
                        No bills for this period. Click "Generate Monthly Bills" to create them.
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $bills->links() }}</div>

    @if ($showPayment)
    <div class="modal-backdrop" wire:click.self="$set('showPayment', false)">
        <div class="modal-panel">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold">Record Payment</h3>
                <button wire:click="$set('showPayment', false)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-ink-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="recordPayment" class="mt-5 space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Amount (৳)</label>
                        <input type="number" wire:model="payment.amount" class="input" step="0.01" required>
                    </div>
                    <div>
                        <label class="label">Date</label>
                        <input type="date" wire:model="payment.payment_date" class="input" required>
                    </div>
                    <div>
                        <label class="label">Method</label>
                        <select wire:model="payment.method" class="input">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                            <option value="rocket">Rocket</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Reference</label>
                        <input type="text" wire:model="payment.reference" class="input">
                    </div>
                </div>
                <div>
                    <label class="label">Notes</label>
                    <textarea wire:model="payment.notes" rows="2" class="input"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="$set('showPayment', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Record &amp; Allocate</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
