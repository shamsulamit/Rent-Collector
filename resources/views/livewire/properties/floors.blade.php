<div>
    <nav class="flex items-center gap-2 text-sm">
        <a href="{{ route('properties.index') }}" class="text-slate-500 hover:text-brand dark:text-slate-400">Properties</a>
        <span class="text-slate-300 dark:text-slate-600">/</span>
        <a href="{{ route('properties.show', $property) }}" class="text-slate-500 hover:text-brand dark:text-slate-400">{{ $property->name }}</a>
        <span class="text-slate-300 dark:text-slate-600">/</span>
        <span class="font-medium">Floors</span>
    </nav>

    <div class="mt-2 flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Floors &mdash; {{ $property->name }}</h1>
        <button wire:click="openCreate" class="btn-primary">Add Floor</button>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($floors as $floor)
            <div class="card p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-bold">{{ $floor->name }}</h3>
                        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ $floor->description ?: 'No description' }}</p>
                    </div>
                    <x-status-badge :label="ucfirst($floor->status)" color="{{ $floor->status === 'active' ? 'emerald' : 'slate' }}" />
                </div>
                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4 dark:border-ink-700">
                    <span class="text-sm text-slate-500">{{ $floor->units_count }} units</span>
                    <div class="flex gap-1">
                        <button wire:click="openEdit('{{ $floor->id }}')" class="btn-ghost px-3 py-1.5 text-xs">Edit</button>
                        <button wire:click="delete('{{ $floor->id }}')" wire:confirm="Delete this floor?" class="btn-ghost px-3 py-1.5 text-xs text-rose-600 dark:text-rose-400">Delete</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="card col-span-full p-10 text-center">
                <p class="font-medium">No floors yet</p>
                <p class="mt-1 text-sm text-slate-400">Floors organize units within a property.</p>
                <button wire:click="openCreate" class="btn-primary mt-4">Add Floor</button>
            </div>
        @endforelse
    </div>

    @if ($showForm)
    <div class="modal-backdrop" wire:click.self="$set('showForm', false)">
        <div class="modal-panel">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold">{{ $editingId ? 'Edit Floor' : 'Add Floor' }}</h3>
                <button wire:click="$set('showForm', false)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-ink-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="save" class="mt-5 space-y-4">
                <div>
                    <label class="label">Floor name / number</label>
                    <input type="text" wire:model="form.name" class="input" placeholder="e.g. Floor 1" required>
                    @error('form.name') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
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
