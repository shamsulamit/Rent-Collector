<div>
    <x-flash />
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Meters</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Electricity, gas and water meters.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('meters.bulk-readings') }}" class="btn-secondary">Gas &amp; water readings</a>
            @can('create', \App\Models\Meter::class)
                <button wire:click="openCreate" class="btn-primary">Add Meter</button>
            @endcan
        </div>
    </div>

    <div class="mt-6 flex flex-wrap gap-2">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search meter number..." class="input max-w-[200px]">
        <select wire:model.live="propertyId" class="input max-w-[190px]">
            <option value="">All properties</option>
            @foreach ($properties as $property)
                <option value="{{ $property->id }}">{{ $property->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="utility" class="input max-w-[150px]">
            <option value="">All utilities</option>
            <option value="electricity">Electricity</option>
            <option value="gas">Gas</option>
            <option value="water">Water</option>
        </select>
        <select wire:model.live="status" class="input max-w-[140px]">
            <option value="active">Active</option>
            <option value="closed">Closed</option>
            <option value="">All</option>
        </select>
    </div>

    <div class="card mt-4 overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Meter</th><th>Utility</th><th>Unit</th><th>Property</th><th>Type</th><th>Readings</th><th>Status</th><th></th></tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-ink-700">
                @forelse ($meters as $meter)
                    <tr>
                        <td class="font-mono text-xs font-semibold">{{ $meter->meter_number }}</td>
                        <td class="capitalize">{{ $meter->utility }}</td>
                        <td>{{ $meter->unit?->name }}</td>
                        <td>{{ $meter->property?->name }}</td>
                        <td class="capitalize">{{ $meter->meter_type }}</td>
                        <td>{{ $meter->readings_count }}</td>
                        <td><x-status-badge :label="ucfirst($meter->status)" color="{{ $meter->status === 'active' ? 'emerald' : 'slate' }}" /></td>
                        <td class="whitespace-nowrap text-right">
                            <button wire:click="openEdit('{{ $meter->id }}')" class="btn-ghost px-2.5 py-1 text-xs">Edit</button>
                            @can('delete', $meter)
                                <button wire:click="delete('{{ $meter->id }}')" wire:confirm="Delete this meter?" class="btn-ghost px-2.5 py-1 text-xs text-rose-600 dark:text-rose-400">Delete</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-8 text-center text-slate-400">No meters found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $meters->links() }}</div>

    @if ($showForm)
    <div class="modal-backdrop" wire:click.self="$set('showForm', false)">
        <div class="modal-panel max-w-xl">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold">{{ $editingId ? 'Edit Meter' : 'Add Meter' }}</h3>
                <button wire:click="$set('showForm', false)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-ink-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="save" class="mt-5 space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
                        <label class="label">Meter number</label>
                        <input type="text" wire:model="form.meter_number" class="input" required>
                    </div>
                    <div>
                        <label class="label">Provider</label>
                        <select wire:model="form.provider" class="input">
                            <option value="">Select</option>
                            @foreach ($providers as $provider)
                                <option value="{{ $provider }}">{{ $provider }}</option>
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
                        <select wire:model.live="form.meter_type" class="input">
                            <option value="postpaid">Postpaid (monthly bill)</option>
                            <option value="prepaid">Prepaid (recharge units)</option>
                        </select>
                    </div>
                    @if ($form['meter_type'] === 'prepaid')
                    <div>
                        <label class="label">Default unit price (৳)</label>
                        <input type="number" wire:model="form.unit_price" class="input" step="0.0001" min="0">
                    </div>
                    @endif
                    <div>
                        <label class="label">Measurement unit</label>
                        <input type="text" wire:model="form.measurement_unit" class="input" placeholder="kWh, m³...">
                    </div>
                    <div>
                        <label class="label">Installation date</label>
                        <input type="date" wire:model="form.installation_date" class="input">
                    </div>
                    <div>
                        <label class="label">Starting reading</label>
                        <input type="number" wire:model="form.starting_reading" class="input" step="0.01">
                    </div>
                    <div>
                        <label class="label">Status</label>
                        <select wire:model="form.status" class="input">
                            <option value="active">Active</option>
                            <option value="closed">Closed</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
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
