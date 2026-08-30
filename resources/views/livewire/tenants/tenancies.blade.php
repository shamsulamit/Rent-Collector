<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Tenancies</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Track every move-in, move-out and rent agreement.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search tenant..." class="input max-w-[180px]">
            <select wire:model.live="propertyId" class="input max-w-[180px]">
                <option value="">All properties</option>
                @foreach ($properties as $property)
                    <option value="{{ $property->id }}">{{ $property->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="status" class="input max-w-[130px]">
                <option value="active">Active</option>
                <option value="ended">Ended</option>
                <option value="">All</option>
            </select>
            <button wire:click="openCreate" class="btn-primary">New Tenancy</button>
        </div>
    </div>

    <div class="card mt-6 overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Tenant</th><th>Property</th><th>Unit</th><th>Move-in</th><th>Move-out</th><th>Rent</th><th>Deposit</th><th>Status</th><th></th></tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-ink-700">
                @forelse ($tenancies as $tenancy)
                    <tr>
                        <td><a href="{{ route('tenants.show', $tenancy->tenant) }}" class="font-medium hover:text-brand">{{ $tenancy->tenant?->full_name }}</a></td>
                        <td>{{ $tenancy->property?->name }}</td>
                        <td>{{ $tenancy->unit?->name }}</td>
                        <td>{{ $tenancy->move_in_date?->format('d M Y') }}</td>
                        <td>{{ $tenancy->move_out_date?->format('d M Y') ?? '—' }}</td>
                        <td class="font-semibold">৳{{ number_format($tenancy->monthly_rent, 0) }}</td>
                        <td>৳{{ number_format($tenancy->deposit, 0) }}</td>
                        <td><x-status-badge :label="ucfirst($tenancy->status)" color="{{ $tenancy->status === 'active' ? 'emerald' : 'slate' }}" /></td>
                        <td>
                            @if ($tenancy->status === 'active')
                                <button wire:click="endTenancy('{{ $tenancy->id }}')" wire:confirm="End this tenancy?" class="btn-danger px-2.5 py-1 text-xs">End</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="py-8 text-center text-slate-400">No tenancies found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $tenancies->links() }}</div>

    @if ($showForm)
    <div class="modal-backdrop" wire:click.self="$set('showForm', false)">
        <div class="modal-panel max-w-xl">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold">New Tenancy</h3>
                <button wire:click="$set('showForm', false)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-ink-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="save" class="mt-5 space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Tenant</label>
                        <select wire:model="form.tenant_id" class="input" required>
                            <option value="">Select tenant</option>
                            @foreach ($tenants as $tenant)
                                <option value="{{ $tenant->id }}">{{ $tenant->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Property</label>
                        <select wire:model.live="form.property_id" class="input" required>
                            <option value="">Select property</option>
                            @foreach ($properties as $property)
                                <option value="{{ $property->id }}">{{ $property->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Unit</label>
                        <select wire:model="form.unit_id" class="input" required>
                            <option value="">Select unit</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Move-in date</label>
                        <input type="date" wire:model="form.move_in_date" class="input" required>
                    </div>
                    <div>
                        <label class="label">Monthly rent (৳)</label>
                        <input type="number" wire:model="form.monthly_rent" class="input" step="0.01" required>
                    </div>
                    <div>
                        <label class="label">Deposit (৳)</label>
                        <input type="number" wire:model="form.deposit" class="input" step="0.01">
                    </div>
                </div>

                <div class="rounded-xl bg-slate-50 p-4 dark:bg-ink-900">
                    <p class="mb-3 text-sm font-semibold">Optional Lease</p>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="label">Start date</label>
                            <input type="date" wire:model="lease.start_date" class="input">
                        </div>
                        <div>
                            <label class="label">End date</label>
                            <input type="date" wire:model="lease.end_date" class="input">
                        </div>
                        <div>
                            <label class="label">Security deposit</label>
                            <input type="number" wire:model="lease.security_deposit" class="input">
                        </div>
                        <div>
                            <label class="label">Status</label>
                            <select wire:model="lease.status" class="input">
                                <option value="active">Active</option>
                                <option value="expired">Expired</option>
                                <option value="terminated">Terminated</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="label">Terms</label>
                        <textarea wire:model="lease.terms" rows="2" class="input"></textarea>
                    </div>
                </div>

                <div>
                    <label class="label">Notes</label>
                    <textarea wire:model="form.notes" rows="2" class="input"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="$set('showForm', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Create Tenancy</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
