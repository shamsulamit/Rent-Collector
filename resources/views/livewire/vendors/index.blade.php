<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Vendors</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Contractors used on expenses and maintenance.</p>
        </div>
        <button wire:click="openCreate" class="btn-primary">Add Vendor</button>
    </div>

    <div class="mt-4">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search vendors..." class="input max-w-[240px]">
    </div>

    <div class="card mt-4 overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Name</th><th>Category</th><th>Phone</th><th>Email</th><th></th></tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-ink-700">
                @forelse ($vendors as $vendor)
                    <tr>
                        <td class="font-medium">{{ $vendor->name }}</td>
                        <td class="capitalize">{{ $vendor->category }}</td>
                        <td>{{ $vendor->phone ?: '—' }}</td>
                        <td>{{ $vendor->email ?: '—' }}</td>
                        <td class="text-right">
                            <button wire:click="openEdit('{{ $vendor->id }}')" class="btn-ghost px-2 py-1 text-xs">Edit</button>
                            @can('delete', $vendor)
                                <button wire:click="delete('{{ $vendor->id }}')" wire:confirm="Delete this vendor?" class="btn-ghost px-2 py-1 text-xs text-rose-600 dark:text-rose-400">Delete</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-10 text-center text-slate-400">No vendors yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $vendors->links() }}</div>

    @if ($showForm)
    <div class="modal-backdrop" wire:click.self="$set('showForm', false)">
        <div class="modal-panel">
            <h3 class="text-lg font-bold">{{ $editingId ? 'Edit Vendor' : 'Add Vendor' }}</h3>
            <form wire:submit="save" class="mt-5 space-y-4">
                <div>
                    <label class="label">Name</label>
                    <input type="text" wire:model="form.name" class="input" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Category</label>
                        <input type="text" wire:model="form.category" class="input">
                    </div>
                    <div>
                        <label class="label">Phone</label>
                        <input type="text" wire:model="form.phone" class="input">
                    </div>
                </div>
                <div>
                    <label class="label">Email</label>
                    <input type="email" wire:model="form.email" class="input">
                </div>
                <div>
                    <label class="label">Address</label>
                    <input type="text" wire:model="form.address" class="input">
                </div>
                <div>
                    <label class="label">Notes</label>
                    <textarea wire:model="form.notes" rows="2" class="input"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="$set('showForm', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
