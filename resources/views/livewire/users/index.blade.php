<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Team</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage who has access to the ledger.</p>
        </div>
        @can('create', \App\Models\User::class)
            <button wire:click="openCreate" class="btn-primary">Add Member</button>
        @endcan
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success mt-4">{{ session('message') }}</div>
    @endif

    <div class="mt-4">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by name or email..." class="input max-w-[260px]">
    </div>

    <div class="card mt-4 overflow-x-auto">
        <table class="table-base">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-ink-700">
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand/10 text-sm font-bold text-brand-dark dark:text-brand-light">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </span>
                                <span class="font-medium">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone ?? '—' }}</td>
                        <td><span class="badge bg-slate-100 capitalize text-slate-600 dark:bg-ink-700 dark:text-slate-300">{{ $user->roles->first()?->name ?? '—' }}</span></td>
                        <td>
                            @if ($user->is_active ?? true)
                                <span class="badge bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Active</span>
                            @else
                                <span class="badge bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">Inactive</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap text-right">
                            <button wire:click="openEdit('{{ $user->id }}')" class="btn-ghost px-2 py-1 text-xs">Edit</button>
                            @if ($user->id !== auth()->id())
                                @can('delete', $user)
                                    <button wire:click="delete('{{ $user->id }}')" wire:confirm="Delete this user?" class="btn-ghost px-2 py-1 text-xs text-rose-600 dark:text-rose-400">Delete</button>
                                @endcan
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-10 text-center text-slate-400">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $users->links() }}</div>

    @if ($showForm)
    <div class="modal-backdrop" wire:click.self="$set('showForm', false)">
        <div class="modal-panel">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold">{{ $editingId ? 'Edit Member' : 'Add Member' }}</h3>
                <button wire:click="$set('showForm', false)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-ink-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="save" class="mt-5 space-y-4">
                <div>
                    <label class="label">Name</label>
                    <input type="text" wire:model="form.name" class="input" required>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Email</label>
                        <input type="email" wire:model="form.email" class="input" required>
                    </div>
                    <div>
                        <label class="label">Phone</label>
                        <input type="text" wire:model="form.phone" class="input">
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">{{ $editingId ? 'New password (leave blank to keep)' : 'Password' }}</label>
                        <input type="password" wire:model="form.password" class="input" @if(!$editingId) required @endif>
                    </div>
                    <div>
                        <label class="label">Role</label>
                        <select wire:model="form.role" class="input">
                            @foreach ($roles as $role)
                                <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                    </div>
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
