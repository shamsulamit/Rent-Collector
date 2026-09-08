<div>
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Audit Log</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">System activity recorded for every important change.</p>
    </div>

    <div class="mt-4 flex flex-wrap gap-2">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search action / entity..." class="input max-w-[240px]">
        <input type="text" wire:model.live.debounce.300ms="action" placeholder="Filter action prefix..." class="input max-w-[200px]">
    </div>

    <div class="card mt-4 overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>When</th><th>User</th><th>Action</th><th>Entity</th><th>IP</th></tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-ink-700">
                @forelse ($logs as $log)
                    <tr>
                        <td class="whitespace-nowrap text-xs">{{ $log->created_at?->format('d M Y H:i') }}</td>
                        <td>{{ $log->user?->name ?? 'System' }}</td>
                        <td class="font-mono text-xs">{{ $log->action }}</td>
                        <td class="text-xs">{{ $log->entity_type }} {{ $log->entity_id ? substr($log->entity_id, 0, 8) : '' }}</td>
                        <td class="text-xs text-slate-400">{{ $log->ip }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-10 text-center text-slate-400">No audit events yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $logs->links() }}</div>
</div>
