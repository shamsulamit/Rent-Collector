<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Tenants</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage your tenants and their tenancy history.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search tenants..." class="input max-w-[220px]">
            <button wire:click="openCreate" class="btn-primary">Add Tenant</button>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($tenants as $tenant)
            <div class="card group transition-all hover:shadow-glow" wire:key="{{ $tenant->id }}">
                <a href="{{ route('tenants.show', $tenant) }}" class="block p-5">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-brand to-emerald-700 font-bold text-white">
                            {{ strtoupper(substr($tenant->full_name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <h3 class="truncate font-bold group-hover:text-brand">{{ $tenant->full_name }}</h3>
                            @if ($tenant->activeTenancy)
                                <p class="truncate text-sm text-slate-500 dark:text-slate-400">
                                    {{ $tenant->activeTenancy->unit?->name }} &middot; {{ $tenant->activeTenancy->property?->name }}
                                </p>
                            @else
                                <p class="text-sm text-slate-400">No active tenancy</p>
                            @endif
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-sm dark:border-ink-700">
                        <span class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h14a2 2 0 012 2v11a2 2 0 01-2 2h-9l-5 4V7a2 2 0 00-2-2h0z"/></svg>
                            {{ $tenant->phone ?: '—' }}
                        </span>
                        <x-status-badge :label="$tenant->activeTenancy ? 'Active' : 'Inactive'" color="{{ $tenant->activeTenancy ? 'emerald' : 'slate' }}" />
                    </div>
                </a>
                <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-3 dark:border-ink-700">
                    <button wire:click="openEdit('{{ $tenant->id }}')" class="btn-ghost px-3 py-1.5 text-xs">Edit</button>
                    @can('delete', $tenant)
                        <button wire:click="delete('{{ $tenant->id }}')" wire:confirm="Delete this tenant?" class="btn-ghost px-3 py-1.5 text-xs text-rose-600 dark:text-rose-400">Delete</button>
                    @endcan
                </div>
            </div>
        @empty
            <div class="card col-span-full p-10 text-center">
                <p class="font-medium">No tenants found</p>
                <button wire:click="openCreate" class="btn-primary mt-4">Add Tenant</button>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $tenants->links() }}</div>

    @if ($showForm)
    <div class="modal-backdrop" wire:click.self="$set('showForm', false)">
        <div class="modal-panel max-w-xl">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold">{{ $editingId ? 'Edit Tenant' : 'Add Tenant' }}</h3>
                <button wire:click="$set('showForm', false)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-ink-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="save" class="mt-5 space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Full name</label>
                        <input type="text" wire:model="form.full_name" class="input" required>
                    </div>
                    <div>
                        <label class="label">Bangla name</label>
                        <input type="text" wire:model="form.bangla_name" class="input">
                    </div>
                    <div>
                        <label class="label">NID</label>
                        <input type="text" wire:model="form.nid" class="input">
                    </div>
                    <div>
                        <label class="label">Passport</label>
                        <input type="text" wire:model="form.passport" class="input">
                    </div>
                    <div>
                        <label class="label">Phone</label>
                        <input type="text" wire:model="form.phone" class="input">
                    </div>
                    <div>
                        <label class="label">WhatsApp number</label>
                        <input type="text" wire:model="form.whatsapp_number" class="input">
                    </div>
                    <div>
                        <label class="label">Email</label>
                        <input type="email" wire:model="form.email" class="input">
                    </div>
                    <div>
                        <label class="label">Emergency contact</label>
                        <input type="text" wire:model="form.emergency_contact" class="input">
                    </div>
                </div>
                <div>
                    <label class="label">Address</label>
                    <textarea wire:model="form.address" rows="2" class="input"></textarea>
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
