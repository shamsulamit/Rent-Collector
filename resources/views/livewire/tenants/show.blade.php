<div>
    <nav class="flex items-center gap-2 text-sm">
        <a href="{{ route('tenants.index') }}" class="text-slate-500 hover:text-brand dark:text-slate-400">Tenants</a>
        <span class="text-slate-300 dark:text-slate-600">/</span>
        <span class="font-medium">{{ $tenant->full_name }}</span>
    </nav>

    <div class="mt-2 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-gradient-to-br from-brand to-emerald-700 text-2xl font-bold text-white">
                {{ strtoupper(substr($tenant->full_name, 0, 1)) }}
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ $tenant->full_name }}</h1>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                    {{ $tenant->phone ?: 'No phone' }} @if($tenant->whatsapp_number) &middot; WhatsApp: {{ $tenant->whatsapp_number }} @endif
                </p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('whatsapp.send', ['tenant' => $tenant, 'template' => 'custom']) }}" class="btn-secondary">
                <svg class="h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                WhatsApp
            </a>
            <button wire:click="openMoveIn" class="btn-primary">Start Tenancy</button>
            <button wire:click="openDeposit" class="btn-secondary">Record Deposit</button>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="stat-card"><p class="text-sm text-slate-500">Outstanding</p><p class="mt-1 text-2xl font-bold {{ $outstanding > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">৳{{ number_format($outstanding, 2) }}</p></div>
        <div class="stat-card"><p class="text-sm text-slate-500">Carry-forward credit</p><p class="mt-1 text-2xl font-bold text-teal-600 dark:text-teal-400">৳{{ number_format($credit, 2) }}</p></div>
        <div class="stat-card"><p class="text-sm text-slate-500">Deposit balance</p><p class="mt-1 text-2xl font-bold">৳{{ number_format($depositBalance, 2) }}</p></div>
        <div class="stat-card"><p class="text-sm text-slate-500">Total tenancies</p><p class="mt-1 text-2xl font-bold">{{ $tenant->tenancies->count() }}</p></div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="card p-5 lg:col-span-2">
            <h2 class="font-semibold">Timeline</h2>
            <div class="mt-4 relative space-y-0">
                @forelse ($timeline as $event)
                    <div class="relative flex gap-4 pb-6">
                        <div class="flex flex-col items-center">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 dark:bg-ink-700 dark:text-slate-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    @if ($event['icon'] === 'key') <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4v-2l5.257-5.257A6 6 0 1121 9z"/>
                                    @elseif ($event['icon'] === 'logout') <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    @elseif ($event['icon'] === 'cash') <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2zm3 8h4"/>
                                    @elseif ($event['icon'] === 'vault') <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    @else <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    @endif
                                </svg>
                            </span>
                            <span class="w-px flex-1 bg-slate-100 dark:bg-ink-700"></span>
                        </div>
                        <div class="pb-1">
                            <p class="text-sm font-medium">{{ $event['title'] }}</p>
                            <p class="text-xs text-slate-400">{{ is_string($event['date']) ? $event['date'] : $event['date']?->format('d M Y') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-400">No timeline events yet.</p>
                @endforelse
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-5">
                <h2 class="font-semibold">Tenancies</h2>
                <div class="mt-3 space-y-3">
                    @forelse ($tenant->tenancies as $tenancy)
                        <div class="rounded-xl border border-slate-100 p-3 dark:border-ink-700">
                            <div class="flex items-center justify-between">
                                <p class="font-medium">{{ $tenancy->unit?->name }}</p>
                                <x-status-badge :label="ucfirst($tenancy->status)" color="{{ $tenancy->status === 'active' ? 'emerald' : 'slate' }}" />
                            </div>
                            <p class="mt-1 text-xs text-slate-400">
                                {{ $tenancy->property?->name }} &middot;
                                {{ $tenancy->move_in_date?->format('d M Y') }} → {{ $tenancy->move_out_date?->format('d M Y') ?? 'present' }}
                            </p>
                            <p class="mt-1 text-sm font-semibold">৳{{ number_format($tenancy->monthly_rent) }}/mo</p>
                            @if ($tenancy->status === 'active')
                                <button wire:click="moveOut('{{ $tenancy->id }}')" wire:confirm="End this tenancy? History will be preserved." class="btn-danger mt-2 w-full px-3 py-1.5 text-xs">End Tenancy</button>
                            @endif
                        </div>
                    @empty
                        <p class="py-3 text-center text-sm text-slate-400">No tenancies yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="card p-5">
                <h2 class="font-semibold">Documents</h2>
                <div class="mt-3 space-y-2">
                    <form wire:submit="uploadDocument" class="space-y-2">
                        <select wire:model="docType" class="input">
                            @foreach ($docTypes as $type)
                                <option value="{{ $type }}">{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                            @endforeach
                        </select>
                        <input type="file" wire:model="document" class="input">
                        <button type="submit" class="btn-primary w-full px-3 py-1.5 text-xs">Upload</button>
                    </form>

                    @forelse ($tenant->documents as $doc)
                        <div class="flex items-center justify-between rounded-xl border border-slate-100 px-3 py-2 dark:border-ink-700">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">{{ $doc->title ?: $doc->type }}</p>
                                <p class="text-xs text-slate-400">{{ number_format($doc->size / 1024, 1) }} KB</p>
                            </div>
                            <a href="{{ route('documents.download', $doc) }}" class="btn-ghost px-2 py-1 text-xs">Download</a>
                        </div>
                    @empty
                        <p class="py-3 text-center text-sm text-slate-400">No documents.</p>
                    @endforelse
                </div>
            </div>

            <div class="card p-5">
                <h2 class="font-semibold">Recent Bills</h2>
                <div class="mt-3 space-y-2">
                    @forelse ($tenant->bills->take(5) as $bill)
                        <a href="{{ route('bills.index', ['month' => $bill->billing_month]) }}" class="flex items-center justify-between rounded-xl border border-slate-100 px-3 py-2 dark:border-ink-700">
                            <span class="text-sm">{{ $bill->billing_month }}</span>
                            <span class="font-semibold">৳{{ number_format($bill->total, 2) }}</span>
                        </a>
                    @empty
                        <p class="py-3 text-center text-sm text-slate-400">No bills.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @if ($showMoveIn)
    <div class="modal-backdrop" wire:click.self="$set('showMoveIn', false)">
        <div class="modal-panel">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold">Start Tenancy</h3>
                <button wire:click="$set('showMoveIn', false)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-ink-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="saveMoveIn" class="mt-5 space-y-4">
                <div>
                    <label class="label">Property</label>
                    <select wire:model.live="moveIn.property_id" class="input" required>
                        <option value="">Select property</option>
                        @foreach ($properties as $property)
                            <option value="{{ $property->id }}">{{ $property->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Unit</label>
                    <select wire:model="moveIn.unit_id" class="input" required>
                        <option value="">Select unit</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }} &middot; ৳{{ number_format($unit->monthly_rent, 0) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="label">Move-in date</label>
                        <input type="date" wire:model="moveIn.move_in_date" class="input" required>
                    </div>
                    <div>
                        <label class="label">Monthly rent</label>
                        <input type="number" wire:model="moveIn.monthly_rent" class="input" step="0.01" required>
                    </div>
                    <div>
                        <label class="label">Deposit</label>
                        <input type="number" wire:model="moveIn.deposit" class="input" step="0.01">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="$set('showMoveIn', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Start Tenancy</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if ($showDeposit)
    <div class="modal-backdrop" wire:click.self="$set('showDeposit', false)">
        <div class="modal-panel">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold">Record Security Deposit</h3>
                <button wire:click="$set('showDeposit', false)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-ink-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="saveDeposit" class="mt-5 space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Type</label>
                        <select wire:model="deposit.type" class="input">
                            <option value="received">Received</option>
                            <option value="additional">Additional</option>
                            <option value="deduction">Deduction</option>
                            <option value="refund">Refund</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Amount</label>
                        <input type="number" wire:model="deposit.amount" class="input" step="0.01" required>
                    </div>
                    <div>
                        <label class="label">Date</label>
                        <input type="date" wire:model="deposit.date" class="input" required>
                    </div>
                    <div>
                        <label class="label">Method</label>
                        <select wire:model="deposit.method" class="input">
                            <option value="">Select</option>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                            <option value="rocket">Rocket</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="label">Reference</label>
                    <input type="text" wire:model="deposit.reference" class="input">
                </div>
                <div>
                    <label class="label">Notes</label>
                    <textarea wire:model="deposit.notes" rows="2" class="input"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="$set('showDeposit', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
