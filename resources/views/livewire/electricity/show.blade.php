<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('electricity.index') }}" class="text-sm text-brand hover:underline">← Electricity bills</a>
            <h1 class="mt-1 text-2xl font-bold tracking-tight">Postpaid electricity bill</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ \Carbon\Carbon::createFromFormat('Y-m', $bill->billing_month)->format('F Y') }}
                · {{ $bill->meter?->meter_number }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('electricity.pdf', $bill) }}" target="_blank" class="btn-secondary">PDF</a>
            @if ($bill->status === 'calculated')
                <button wire:click="finalize" wire:confirm="Finalize this bill? It cannot be recalculated with a later tariff." class="btn-primary">Finalize</button>
            @endif
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="card p-5 lg:col-span-2">
            <h2 class="font-semibold">Bill record</h2>
            <dl class="mt-4 grid grid-cols-2 gap-x-4 gap-y-3 text-sm sm:grid-cols-3">
                <div>
                    <dt class="text-xs uppercase text-slate-400">Property</dt>
                    <dd class="font-medium">{{ $bill->property?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-400">Unit</dt>
                    <dd class="font-medium">{{ $bill->unit?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-400">Tenant</dt>
                    <dd class="font-medium">{{ $bill->tenant?->full_name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-400">Meter</dt>
                    <dd class="font-mono text-xs">{{ $bill->meter?->meter_number }} · {{ $bill->meter?->provider }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-400">Tariff</dt>
                    <dd>{{ $bill->tariff?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-400">Status</dt>
                    <dd><x-status-badge :label="ucfirst($bill->status)" color="{{ $bill->status }}" /></dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-400">Previous reading</dt>
                    <dd class="tabular-nums">{{ number_format($bill->previous_reading, 1) }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-400">Current reading</dt>
                    <dd class="tabular-nums">{{ number_format($bill->current_reading, 1) }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-400">Usage</dt>
                    <dd class="font-semibold tabular-nums">{{ number_format($bill->usage, 1) }} units</dd>
                </div>
                @if ($bill->finalized_at)
                    <div>
                        <dt class="text-xs uppercase text-slate-400">Finalized</dt>
                        <dd>{{ $bill->finalized_at->format('d M Y') }}</dd>
                    </div>
                @endif
            </dl>

            <table class="table-base mt-6">
                <thead><tr><th>Charge</th><th class="text-right">Amount (৳)</th></tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-ink-700">
                    @foreach ($bill->lineItems() as $item)
                        <tr>
                            <td>{{ $item['label'] }}</td>
                            <td class="text-right tabular-nums">{{ number_format($item['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="font-bold">
                        <td>Total</td>
                        <td class="text-right tabular-nums">৳{{ number_format($bill->total, 2) }}</td>
                    </tr>
                </tbody>
            </table>
            <p class="mt-3 text-xs text-slate-400">Usage = current − previous. This amount rolls into the tenant monthly bill after you generate monthly bills.</p>
        </div>

        <div class="card p-5">
            <h2 class="font-semibold">Tariff slabs</h2>
            @if ($bill->tariff?->slabs)
                <ul class="mt-3 space-y-2 text-sm">
                    @foreach ($bill->tariff->slabs as $slab)
                        <li class="flex justify-between rounded-lg bg-slate-50 px-3 py-2 dark:bg-ink-900">
                            <span>{{ $slab['min'] }}–{{ $slab['max'] ?? '+' }} units</span>
                            <span class="tabular-nums">৳{{ number_format($slab['rate'] ?? 0, 2) }}</span>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-3 text-xs text-slate-400">Changing a tariff later never recalculates a finalized bill.</p>
            @else
                <p class="mt-3 text-sm text-slate-400">No tariff linked. Add one under Tariffs, then re-submit readings for draft/calculated bills.</p>
            @endif
        </div>
    </div>
</div>
