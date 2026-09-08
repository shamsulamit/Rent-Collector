<div>
    <x-flash />
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Settings</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Company profile, defaults and accounting periods.</p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success mt-4">{{ session('message') }}</div>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="card p-5">
            <h3 class="font-bold">Company &amp; Billing Defaults</h3>
            <p class="mt-1 text-sm text-slate-500">Rate tables for gas/water live here. <a href="{{ route('tariffs.index') }}" class="text-brand hover:underline">Manage tariffs</a></p>
            <form wire:submit="save" class="mt-4 space-y-4">
                <div>
                    <label class="label">Company name</label>
                    <input type="text" wire:model="companyName" class="input">
                </div>
                <div>
                    <label class="label">WhatsApp message language</label>
                    <select wire:model="whatsappLocale" class="input">
                        <option value="en">English</option>
                        <option value="bn">Bangla</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    @foreach (['waste' => 'Waste', 'security' => 'Security', 'cleaning' => 'Cleaning', 'internet' => 'Internet', 'parking' => 'Parking', 'other' => 'Other'] as $key => $label)
                        <div>
                            <label class="label">{{ $label }} (৳/month)</label>
                            <input type="number" wire:model="recurring.{{ $key }}" class="input" step="0.01" min="0">
                        </div>
                    @endforeach
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Gas unit rate (৳)</label>
                        <input type="number" wire:model="gasRate" class="input" step="0.01" min="0">
                    </div>
                    <div>
                        <label class="label">Water unit rate (৳)</label>
                        <input type="number" wire:model="waterRate" class="input" step="0.01" min="0">
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="btn-primary">Save Settings</button>
                </div>
            </form>
        </div>

        <div class="card p-5">
            <h3 class="font-bold">Close Accounting Period</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Locking a month makes its financial records immutable.</p>
            <form wire:submit="closePeriod" class="mt-4 flex flex-wrap items-end gap-3">
                <div>
                    <label class="label">Month</label>
                    <input type="month" wire:model="closeMonth" class="input">
                </div>
                <button type="submit" wire:confirm="Close this accounting period? Its records become immutable."
                    class="btn-ghost border border-amber-300 text-amber-700 hover:bg-amber-50 dark:border-amber-700 dark:text-amber-300 dark:hover:bg-amber-900/20">Close Period</button>
            </form>

            <h3 class="mt-6 font-bold">Closed Periods</h3>
            <div class="mt-2 overflow-x-auto">
                <table class="table-base">
                    <thead><tr><th>Period</th><th>Status</th><th>Closed by</th><th>Closed at</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-ink-700">
                        @forelse ($periods as $period)
                            <tr>
                                <td>{{ $period->period }}</td>
                                <td><span class="badge bg-slate-100 capitalize text-slate-600 dark:bg-ink-700 dark:text-slate-300">{{ $period->status }}</span></td>
                                <td>{{ $period->closer?->name ?? '—' }}</td>
                                <td>{{ $period->closed_at }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-8 text-center text-slate-400">No closed periods.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $periods->links() }}</div>
        </div>
    </div>
</div>
