<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Expenses</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Track property operating expenses.</p>
        </div>
        <button wire:click="openCreate" class="btn-primary">Add Expense</button>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-2">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search description..." class="input max-w-[200px]">
        <select wire:model.live="propertyId" class="input max-w-[180px]">
            <option value="">All properties</option>
            @foreach ($properties as $property)
                <option value="{{ $property->id }}">{{ $property->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="category" class="input max-w-[160px]">
            <option value="">All categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category }}">{{ ucfirst($category) }}</option>
            @endforeach
        </select>
        <input type="date" wire:model.live="from" class="input max-w-[150px]">
        <input type="date" wire:model.live="to" class="input max-w-[150px]">
        <span class="rounded-xl bg-brand/10 px-3 py-2 text-sm font-semibold text-brand-dark dark:text-brand-light">Total: ৳{{ number_format($total, 2) }}</span>
    </div>

    <div class="card mt-4 overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Date</th><th>Category</th><th>Description</th><th>Property</th><th>Unit</th><th>Vendor</th><th>Method</th><th class="text-right">Amount</th><th></th></tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-ink-700">
                @forelse ($expenses as $expense)
                    <tr>
                        <td>{{ $expense->expense_date->format('d M Y') }}</td>
                        <td><span class="badge bg-slate-100 capitalize text-slate-600 dark:bg-ink-700 dark:text-slate-300">{{ $expense->category }}</span></td>
                        <td class="max-w-[220px] truncate">{{ $expense->description ?: '—' }}</td>
                        <td>{{ $expense->property?->name }}</td>
                        <td>{{ $expense->unit?->name ?? '—' }}</td>
                        <td>{{ $expense->vendor?->name ?? '—' }}</td>
                        <td class="capitalize">{{ $expense->payment_method }}</td>
                        <td class="text-right font-semibold tabular-nums">৳{{ number_format($expense->amount, 2) }}</td>
                        <td class="whitespace-nowrap text-right">
                            <button wire:click="openEdit('{{ $expense->id }}')" class="btn-ghost px-2 py-1 text-xs">Edit</button>
                            @can('delete', $expense)
                                <button wire:click="delete('{{ $expense->id }}')" wire:confirm="Delete this expense?" class="btn-ghost px-2 py-1 text-xs text-rose-600 dark:text-rose-400">Delete</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="py-10 text-center text-slate-400">No expenses recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $expenses->links() }}</div>

    @if ($showForm)
    <div class="modal-backdrop" wire:click.self="$set('showForm', false)">
        <div class="modal-panel">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold">{{ $editingId ? 'Edit Expense' : 'Add Expense' }}</h3>
                <button wire:click="$set('showForm', false)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-ink-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="save" class="mt-5 space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Category</label>
                        <select wire:model="form.category" class="input">
                            @foreach ($categories as $category)
                                <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Amount (৳)</label>
                        <input type="number" wire:model="form.amount" class="input" step="0.01" required>
                    </div>
                    <div>
                        <label class="label">Date</label>
                        <input type="date" wire:model="form.expense_date" class="input" required>
                    </div>
                    <div>
                        <label class="label">Payment method</label>
                        <select wire:model="form.payment_method" class="input">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                            <option value="rocket">Rocket</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Property</label>
                        <select wire:model="form.property_id" class="input">
                            <option value="">Select</option>
                            @foreach ($properties as $property)
                                <option value="{{ $property->id }}">{{ $property->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Vendor</label>
                        <select wire:model="form.vendor_id" class="input">
                            <option value="">Select</option>
                            @foreach ($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="label">Description</label>
                    <input type="text" wire:model="form.description" class="input">
                </div>
                <div>
                    <label class="label">Notes</label>
                    <textarea wire:model="form.notes" rows="2" class="input"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="$set('showForm', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
