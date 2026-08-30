<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Reports</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Financial summaries for the selected period.</p>
        </div>
    </div>

    <div class="card mt-4 flex flex-wrap items-end gap-4">
        <div>
            <label class="label">Property</label>
            <select wire:model.live="propertyId" class="input max-w-[200px]">
                <option value="">All properties</option>
                @foreach ($properties as $property)
                    <option value="{{ $property->id }}">{{ $property->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">From</label>
            <input type="date" wire:model.live="from" class="input">
        </div>
        <div>
            <label class="label">To</label>
            <input type="date" wire:model.live="to" class="input">
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="stat-card">
            <p class="stat-label">Total Billed</p>
            <p class="stat-value">৳{{ number_format($totals['billed'], 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Total Collected</p>
            <p class="stat-value">৳{{ number_format($totals['collected'], 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Net Income</p>
            <p class="stat-value">৳{{ number_format($totals['net'], 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Expenses</p>
            <p class="stat-value">৳{{ number_format($totals['expenses'], 2) }}</p>
        </div>
    </div>

    <div class="card mt-6 p-5">
        <h3 class="font-bold">Property Summary</h3>
        <div class="mt-3 overflow-x-auto">
            <table class="table-base">
                <thead><tr><th>Property</th><th class="text-right">Billed</th><th class="text-right">Collected</th><th class="text-right">Expenses</th><th class="text-right">Net</th><th class="text-right">Occupancy</th></tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-ink-700">
                    @forelse ($propertyRows as $row)
                        <tr>
                            <td class="font-medium">{{ $row['name'] }}</td>
                            <td class="text-right tabular-nums">৳{{ number_format($row['billed'], 2) }}</td>
                            <td class="text-right tabular-nums">৳{{ number_format($row['collected'], 2) }}</td>
                            <td class="text-right tabular-nums">৳{{ number_format($row['expenses'], 2) }}</td>
                            <td class="text-right tabular-nums">৳{{ number_format($row['net'], 2) }}</td>
                            <td class="text-right tabular-nums">{{ $row['occupancy'] }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-slate-400">No data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="card p-5">
            <h3 class="font-bold">Payment Methods</h3>
            @forelse ($paymentMethods as $method => $total)
                <div class="mt-3 flex items-center justify-between border-b border-slate-100 pb-2 dark:border-ink-700">
                    <span class="capitalize text-slate-600 dark:text-slate-300">{{ $method }}</span>
                    <span class="font-semibold tabular-nums">৳{{ number_format($total, 2) }}</span>
                </div>
            @empty
                <p class="mt-3 text-sm text-slate-400">No payments in this period.</p>
            @endforelse
        </div>

        <div class="card p-5">
            <h3 class="font-bold">Expense Breakdown</h3>
            @forelse ($expenseBreakdown as $category => $total)
                <div class="mt-3 flex items-center justify-between border-b border-slate-100 pb-2 dark:border-ink-700">
                    <span class="capitalize text-slate-600 dark:text-slate-300">{{ $category }}</span>
                    <span class="font-semibold tabular-nums">৳{{ number_format($total, 2) }}</span>
                </div>
            @empty
                <p class="mt-3 text-sm text-slate-400">No expenses in this period.</p>
            @endforelse
        </div>
    </div>

    <div class="card mt-4 flex flex-wrap items-center justify-between p-5">
        <div>
            <h3 class="font-bold">Printable Report</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Download the property summary as a PDF for the selected period.</p>
        </div>
        <a href="{{ route('reports.pdf', ['report' => 'property', 'from' => $from, 'to' => $to, 'property_id' => $propertyId]) }}" target="_blank" class="btn-primary">Download PDF</a>
    </div>
</div>
