<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Maintenance</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Track maintenance tickets from open to completed.</p>
        </div>
        <button wire:click="openCreate" class="btn-primary">New Ticket</button>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-2">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search ticket / title..." class="input max-w-[200px]">
        <select wire:model.live="status" class="input max-w-[140px]">
            <option value="">All statuses</option>
            <option value="open">Open</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>
        <select wire:model.live="priority" class="input max-w-[140px]">
            <option value="">All priorities</option>
            <option value="low">Low</option>
            <option value="normal">Normal</option>
            <option value="high">High</option>
            <option value="urgent">Urgent</option>
        </select>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        @forelse ($tickets as $ticket)
            <div class="card p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs text-slate-400">{{ $ticket->ticket_no }}</span>
                            <x-status-badge :label="ucfirst(str_replace('_', ' ', $ticket->status))" color="{{ match ($ticket->status) { 'open' => 'orange', 'in_progress' => 'sky', 'completed' => 'emerald', 'cancelled' => 'slate', default => 'slate' } }}" />
                            <x-status-badge :label="ucfirst($ticket->priority)" color="{{ match ($ticket->priority) { 'urgent' => 'rose', 'high' => 'orange', 'normal' => 'amber', 'low' => 'slate', default => 'slate' } }}" />
                        </div>
                        <h3 class="mt-2 font-bold">{{ $ticket->title }}</h3>
                        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                            {{ $ticket->property?->name }} @if($ticket->unit) / {{ $ticket->unit->name }} @endif
                            @if($ticket->tenant) / {{ $ticket->tenant->full_name }} @endif
                        </p>
                    </div>
                </div>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $ticket->description ?: 'No description' }}</p>
                <div class="mt-3 flex flex-wrap gap-3 text-sm">
                    @if ($ticket->estimated_cost)
                        <span class="text-slate-500 dark:text-slate-400">Est: <strong>৳{{ number_format($ticket->estimated_cost) }}</strong></span>
                    @endif
                    @if ($ticket->actual_cost)
                        <span class="text-slate-500 dark:text-slate-400">Actual: <strong>৳{{ number_format($ticket->actual_cost) }}</strong></span>
                    @endif
                    @if ($ticket->vendor)
                        <span class="text-slate-500 dark:text-slate-400">Vendor: <strong>{{ $ticket->vendor->name }}</strong></span>
                    @endif
                </div>
                <div class="mt-4 flex flex-wrap gap-2 border-t border-slate-100 pt-4 dark:border-ink-700">
                    @if ($ticket->status === 'open')
                        <button wire:click="setStatus('{{ $ticket->id }}', 'in_progress')" class="btn-ghost px-3 py-1.5 text-xs">Start</button>
                        <button wire:click="setStatus('{{ $ticket->id }}', 'cancelled')" class="btn-ghost px-3 py-1.5 text-xs text-rose-500">Cancel</button>
                    @endif
                    @if ($ticket->status === 'in_progress')
                        <button wire:click="setStatus('{{ $ticket->id }}', 'completed')" class="btn-ghost px-3 py-1.5 text-xs text-emerald-600 dark:text-emerald-400">Complete</button>
                    @endif
                    <button wire:click="openEdit('{{ $ticket->id }}')" class="btn-ghost px-3 py-1.5 text-xs">Edit</button>
                </div>
            </div>
        @empty
            <div class="card col-span-full p-10 text-center">
                <p class="font-medium">No maintenance tickets</p>
                <button wire:click="openCreate" class="btn-primary mt-4">New Ticket</button>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $tickets->links() }}</div>

    @if ($showForm)
    <div class="modal-backdrop" wire:click.self="$set('showForm', false)">
        <div class="modal-panel max-w-xl">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold">{{ $editingId ? 'Edit Ticket' : 'New Maintenance Ticket' }}</h3>
                <button wire:click="$set('showForm', false)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-ink-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="save" class="mt-5 space-y-4">
                <div>
                    <label class="label">Title</label>
                    <input type="text" wire:model="form.title" class="input" required>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Property</label>
                        <select wire:model.live="form.property_id" class="input">
                            <option value="">Select</option>
                            @foreach ($properties as $property)
                                <option value="{{ $property->id }}">{{ $property->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Unit</label>
                        <select wire:model="form.unit_id" class="input">
                            <option value="">Select</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Tenant</label>
                        <select wire:model="form.tenant_id" class="input">
                            <option value="">Select</option>
                            @foreach ($tenants as $tenant)
                                <option value="{{ $tenant->id }}">{{ $tenant->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Vendor</label>
                        <select wire:model="form.vendor_id" class="input">
                            <option value="">Select</option>
                            @foreach ($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Priority</label>
                        <select wire:model="form.priority" class="input">
                            <option value="low">Low</option>
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Opened date</label>
                        <input type="date" wire:model="form.opened_at" class="input" required>
                    </div>
                    <div>
                        <label class="label">Estimated cost</label>
                        <input type="number" wire:model="form.estimated_cost" class="input" step="0.01">
                    </div>
                    <div>
                        <label class="label">Actual cost</label>
                        <input type="number" wire:model="form.actual_cost" class="input" step="0.01">
                    </div>
                </div>
                <div>
                    <label class="label">Description</label>
                    <textarea wire:model="form.description" rows="2" class="input"></textarea>
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
