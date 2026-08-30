<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Backups</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Encrypted database backups, local and Google Drive.</p>
        </div>
        <button wire:click="backupNow" wire:loading.attr="disabled" class="btn-primary">
            <span wire:loading wire:target="backupNow">Backing up...</span>
            <span wire:loading.remove wire:target="backupNow">Backup Now</span>
        </button>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success mt-4">{{ session('message') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-error mt-4">{{ session('error') }}</div>
    @endif

    @if ($drivesAvailable)
        <div class="card mt-4 p-5">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-brand" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 19h20L12 2zm0 4l5.5 9H6.5L12 6z"/></svg>
                <h3 class="font-bold">Google Drive</h3>
                <span class="badge bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Connected</span>
            </div>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Offsite copies are synced to your linked Drive account.</p>
            @if (count($driveFiles) > 0)
                <ul class="mt-3 space-y-1 text-sm">
                    @foreach ($driveFiles as $file)
                        <li class="flex items-center justify-between border-b border-slate-100 pb-1 dark:border-ink-700">
                            <span class="font-mono text-xs">{{ $file['name'] }}</span>
                            <span class="text-slate-400">{{ $file['size'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <div class="card mt-4 overflow-x-auto">
        <table class="table-base">
            <thead>
                <tr>
                    <th>File</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Created</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-ink-700">
                @forelse ($records as $record)
                    <tr>
                        <td class="font-mono text-xs">{{ $record->filename }}</td>
                        <td><span class="badge bg-slate-100 capitalize text-slate-600 dark:bg-ink-700 dark:text-slate-300">{{ $record->type }}</span></td>
                        <td>{{ $record->size ? number_format($record->size / 1024, 1) . ' KB' : '—' }}</td>
                        <td>{{ $record->created_at->format('d M Y H:i') }}</td>
                        <td class="text-right">
                            @if ($record->status === 'completed')
                                <button wire:click="restore('{{ $record->id }}')" wire:confirm="Restore this backup? This overwrites current data."
                                    class="btn-ghost px-2 py-1 text-xs text-amber-600 dark:text-amber-400">Restore</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-10 text-center text-slate-400">No backups yet. Run your first backup.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $records->links() }}</div>

    <p class="mt-4 text-xs text-slate-400">Retention: {{ $retention }} backups kept locally.</p>
</div>
