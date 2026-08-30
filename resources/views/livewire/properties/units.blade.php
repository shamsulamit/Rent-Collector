<div>
    <nav class="flex items-center gap-2 text-sm">
        <a href="{{ route('properties.index') }}" class="text-slate-500 hover:text-brand dark:text-slate-400">Properties</a>
        <span class="text-slate-300 dark:text-slate-600">/</span>
        <a href="{{ route('properties.show', $property) }}" class="text-slate-500 hover:text-brand dark:text-slate-400">{{ $property->name }}</a>
        <span class="text-slate-300 dark:text-slate-600">/</span>
        <span class="font-medium">Units</span>
    </nav>

    <div class="mt-2 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Units &mdash; {{ $property->name }}</h1>
        <div class="flex flex-wrap items-center gap-2">
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search units..." class="input max-w-[180px]">
            <select wire:model.live="floorId" class="input max-w-[160px]">
                <option value="">All floors</option>
                @foreach ($floors as $floor)
                    <option value="{{ $floor->id }}">{{ $floor->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="status" class="input max-w-[150px]">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </select>
            <button wire:click="openCreate" class="btn-primary">Add Unit</button>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($units as $unit)
            <div class="card p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-bold">{{ $unit->name }}</h3>
                        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                            {{ $unit->floor?->name ?? 'No floor' }} &middot; {{ $unit->unit_type ?: 'Unit' }}
                        </p>
                    </div>
                    <x-status-badge :label="ucfirst($unit->status)" color="{{ ['vacant' => 'gray', 'occupied' => 'emerald', 'reserved' => 'amber', 'maintenance' => 'orange', 'inactive' => 'slate'][$unit->status] ?? 'slate' }}" />
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-3 dark:bg-ink-900">
                    <div><p class="text-xs text-slate-400">Rent</p><p class="font-semibold">৳{{ number_format($unit->monthly_rent, 0) }}</p></div>
                    <div><p class="text-xs text-slate-400">Tenant</p><p class="truncate font-semibold">{{ $unit->activeTenancy?->tenant?->full_name ?? 'Vacant' }}</p></div>
                </div>

                <div class="mt-3 flex flex-wrap gap-1.5">
                    <span class="badge bg-slate-100 text-slate-600 dark:bg-ink-700 dark:text-slate-300">{{ $unit->bedrooms }} BD</span>
                    <span class="badge bg-slate-100 text-slate-600 dark:bg-ink-700 dark:text-slate-300">{{ $unit->bathrooms }} BA</span>
                    @if ($unit->size)<span class="badge bg-slate-100 text-slate-600 dark:bg-ink-700 dark:text-slate-300">{{ $unit->size }} sqft</span>@endif
                    <span class="badge bg-slate-100 text-slate-600 dark:bg-ink-700 dark:text-slate-300">{{ $unit->meters_count ?? $unit->meters->count() }} meters</span>
                </div>

                <div class="mt-4 flex justify-end gap-2 border-t border-slate-100 pt-4 dark:border-ink-700">
                    <button wire:click="openEdit('{{ $unit->id }}')" class="btn-ghost px-3 py-1.5 text-xs">Edit</button>
                </div>
            </div>
        @empty
            <div class="card col-span-full p-10 text-center">
                <p class="font-medium">No units found</p>
                <button wire:click="openCreate" class="btn-primary mt-4">Add Unit</button>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $units->links() }}</div>

    @if ($showForm)
    <div class="modal-backdrop" wire:click.self="$set('showForm', false)">
        <div class="modal-panel">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold">{{ $editingId ? 'Edit Unit' : 'Add Unit' }}</h3>
                <button wire:click="$set('showForm', false)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-ink-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="save" class="mt-5 space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Unit name / number</label>
                        <input type="text" wire:model="form.name" class="input" placeholder="e.g. A-101" required>
                    </div>
                    <div>
                        <label class="label">Floor</label>
                        <select wire:model="form.floor_id" class="input">
                            <option value="">No floor</option>
                            @foreach ($floors as $floor)
                                <option value="{{ $floor->id }}">{{ $floor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Unit type</label>
                        <input type="text" wire:model="form.unit_type" class="input" placeholder="Apartment, Shop...">
                    </div>
                    <div>
                        <label class="label">Size (sqft)</label>
                        <input type="number" wire:model="form.size" class="input">
                    </div>
                    <div>
                        <label class="label">Bedrooms</label>
                        <input type="number" wire:model="form.bedrooms" class="input" min="0">
                    </div>
                    <div>
                        <label class="label">Bathrooms</label>
                        <input type="number" wire:model="form.bathrooms" class="input" min="0">
                    </div>
                    <div>
                        <label class="label">Monthly rent (৳)</label>
                        <input type="number" wire:model="form.monthly_rent" class="input" min="0" step="0.01" required>
                    </div>
                    <div>
                        <label class="label">Status</label>
                        <select wire:model="form.status" class="input">
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
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
