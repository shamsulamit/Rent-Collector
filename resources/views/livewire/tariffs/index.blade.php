<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Tariffs</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Historical rates are preserved. New tariffs never rewrite old bills.</p>
        </div>
        <button wire:click="openCreate" class="btn-primary">Add Tariff</button>
    </div>

    <div class="mt-4 flex flex-wrap gap-2">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search name / provider..." class="input max-w-[220px]">
        <select wire:model.live="utility" class="input max-w-[160px]">
            <option value="">All utilities</option>
            <option value="electricity">Electricity</option>
            <option value="gas">Gas</option>
            <option value="water">Water</option>
        </select>
    </div>

    <div class="card mt-4 overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Name</th><th>Provider</th><th>Utility</th><th>Type</th><th>Effective</th><th>VAT</th><th>Status</th><th></th></tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-ink-700">
                @forelse ($tariffs as $tariff)
                    <tr>
                        <td class="font-medium">{{ $tariff->name }}</td>
                        <td>{{ $tariff->provider }}</td>
                        <td class="capitalize">{{ $tariff->utility }}</td>
                        <td class="capitalize">{{ $tariff->meter_type }}</td>
                        <td>{{ optional($tariff->effective_date)->format('d M Y') ?? '—' }}</td>
                        <td>{{ number_format($tariff->vat_rate, 1) }}%</td>
                        <td><x-status-badge :label="$tariff->is_active ? 'Active' : 'Inactive'" color="{{ $tariff->is_active ? 'emerald' : 'slate' }}" /></td>
                        <td class="text-right">
                            <button wire:click="openEdit('{{ $tariff->id }}')" class="btn-ghost px-2 py-1 text-xs">Edit</button>
                            @can('delete', $tariff)
                                <button wire:click="delete('{{ $tariff->id }}')" wire:confirm="Delete this tariff?" class="btn-ghost px-2 py-1 text-xs text-rose-600 dark:text-rose-400">Delete</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-10 text-center text-slate-400">No tariffs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $tariffs->links() }}</div>

    @if ($showForm)
    <div class="modal-backdrop" wire:click.self="$set('showForm', false)">
        <div class="modal-panel max-w-2xl">
            <h3 class="text-lg font-bold">{{ $editingId ? 'Edit Tariff' : 'New Tariff' }}</h3>
            <form wire:submit="save" class="mt-5 space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="label">Name</label>
                        <input type="text" wire:model="form.name" class="input" required>
                    </div>
                    <div>
                        <label class="label">Provider</label>
                        <select wire:model="form.provider" class="input">
                            @foreach ($providers as $p)
                                <option value="{{ $p }}">{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Utility</label>
                        <select wire:model="form.utility" class="input">
                            <option value="electricity">Electricity</option>
                            <option value="gas">Gas</option>
                            <option value="water">Water</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Meter type</label>
                        <select wire:model="form.meter_type" class="input">
                            <option value="postpaid">Postpaid</option>
                            <option value="prepaid">Prepaid</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Effective date</label>
                        <input type="date" wire:model="form.effective_date" class="input">
                    </div>
                    <div>
                        <label class="label">Expiry date</label>
                        <input type="date" wire:model="form.expiry_date" class="input">
                    </div>
                    <div>
                        <label class="label">Fixed charge</label>
                        <input type="number" step="0.01" wire:model="form.fixed_charge" class="input">
                    </div>
                    <div>
                        <label class="label">Service charge</label>
                        <input type="number" step="0.01" wire:model="form.service_charge" class="input">
                    </div>
                    <div>
                        <label class="label">Demand charge</label>
                        <input type="number" step="0.01" wire:model="form.demand_charge" class="input">
                    </div>
                    <div>
                        <label class="label">VAT %</label>
                        <input type="number" step="0.01" wire:model="form.vat_rate" class="input">
                    </div>
                    <div>
                        <label class="label">Other charge</label>
                        <input type="number" step="0.01" wire:model="form.other_charge" class="input">
                    </div>
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-sm font-semibold">Consumption slabs</p>
                        <button type="button" wire:click="addSlab" class="btn-ghost px-2 py-1 text-xs">Add slab</button>
                    </div>
                    <div class="space-y-2">
                        @foreach ($slabs as $i => $slab)
                            <div class="grid grid-cols-7 gap-2">
                                <input type="number" wire:model="slabs.{{ $i }}.min" class="input col-span-2" placeholder="Min">
                                <input type="number" wire:model="slabs.{{ $i }}.max" class="input col-span-2" placeholder="Max (blank = +)">
                                <input type="number" step="0.01" wire:model="slabs.{{ $i }}.rate" class="input col-span-2" placeholder="Rate">
                                <button type="button" wire:click="removeSlab({{ $i }})" class="btn-ghost px-2 text-rose-500">×</button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model="form.is_active" class="rounded border-slate-300 text-brand"> Active
                </label>

                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="$set('showForm', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
