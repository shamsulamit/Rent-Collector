<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Properties</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $properties->total() }} properties in your portfolio.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search properties..." class="input max-w-[220px]">
            <select wire:model.live="status" class="input max-w-[140px]">
                <option value="">All statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <button wire:click="openCreate" class="btn-primary">Add Property</button>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($properties as $property)
            <div class="card group p-5 transition-all hover:shadow-glow" wire:key="{{ $property->id }}">
                <div class="flex items-start justify-between">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-brand to-emerald-700 text-white shadow-lg shadow-brand/20">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 21V10m4 11V10m4 11V10m4 11V10m4 11V10M2 10l10-7 10 7"/></svg>
                    </div>
                    <x-status-badge :label="ucfirst($property->status)" color="{{ $property->status === 'active' ? 'emerald' : 'slate' }}" />
                </div>

                <a href="{{ route('properties.show', $property) }}" class="mt-4 block">
                    <h3 class="text-lg font-bold hover:text-brand">{{ $property->name }}</h3>
                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                        {{ collect([$property->address, $property->city, $property->area])->filter()->implode(', ') ?: 'No address set' }}
                    </p>
                </a>

                <div class="mt-4 grid grid-cols-3 gap-2 border-t border-slate-100 pt-4 text-center dark:border-ink-700">
                    <div>
                        <p class="text-lg font-bold">{{ $property->units_count }}</p>
                        <p class="text-xs text-slate-400">Units</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $property->occupied_units_count }}</p>
                        <p class="text-xs text-slate-400">Occupied</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ $property->tenancies_count }}</p>
                        <p class="text-xs text-slate-400">Tenancies</p>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('properties.show', $property) }}" class="btn-ghost flex-1">View</a>
                    <a href="{{ route('properties.floors', $property) }}" class="btn-ghost flex-1">Floors</a>
                    <a href="{{ route('properties.units', $property) }}" class="btn-ghost flex-1">Units</a>
                    <button wire:click="openEdit('{{ $property->id }}')" class="btn-ghost flex-1">Edit</button>
                    @can('delete', $property)
                        <button wire:click="delete('{{ $property->id }}')" wire:confirm="Delete this property?" class="btn-ghost flex-1 text-rose-600 dark:text-rose-400">Delete</button>
                    @endcan
                </div>
            </div>
        @empty
            <div class="card col-span-full p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 21V10m4 11V10m4 11V10m4 11V10m4 11V10M2 10l10-7 10 7"/></svg>
                <p class="mt-3 font-medium">No properties yet</p>
                <p class="mt-1 text-sm text-slate-400">Add your first property to get started.</p>
                <button wire:click="openCreate" class="btn-primary mt-4">Add Property</button>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $properties->links() }}</div>

    @if ($showForm)
    <div class="modal-backdrop" wire:click.self="$set('showForm', false)">
        <div class="modal-panel">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold">{{ $editingId ? 'Edit Property' : 'Add Property' }}</h3>
                <button wire:click="$set('showForm', false)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-ink-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form wire:submit="save" class="mt-5 space-y-4">
                <div>
                    <label class="label">Name</label>
                    <input type="text" wire:model="form.name" class="input" required>
                    @error('form.name') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Address</label>
                        <input type="text" wire:model="form.address" class="input">
                    </div>
                    <div>
                        <label class="label">City</label>
                        <input type="text" wire:model="form.city" class="input">
                    </div>
                    <div>
                        <label class="label">Area</label>
                        <input type="text" wire:model="form.area" class="input">
                    </div>
                    <div>
                        <label class="label">Postal code</label>
                        <input type="text" wire:model="form.postal_code" class="input">
                    </div>
                    <div>
                        <label class="label">Contact phone</label>
                        <input type="text" wire:model="form.contact_phone" class="input">
                    </div>
                    <div>
                        <label class="label">Contact email</label>
                        <input type="email" wire:model="form.contact_email" class="input">
                    </div>
                </div>

                <div>
                    <label class="label">Description</label>
                    <textarea wire:model="form.description" rows="2" class="input"></textarea>
                </div>

                <div>
                    <label class="label">Status</label>
                    <select wire:model="form.status" class="input">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
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
