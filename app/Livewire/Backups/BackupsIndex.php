<?php

namespace App\Livewire\Backups;

use App\Models\BackupRecord;
use App\Services\Backup\BackupService;
use App\Services\Backup\GoogleDriveService;
use Livewire\Component;

class BackupsIndex extends Component
{
    public bool $loading = false;

    protected $listeners = ['backupFinished' => '$refresh'];

    public function backupNow(): void
    {
        $this->authorize('create', BackupRecord::class);
        $this->loading = true;

        try {
            $record = app(BackupService::class)->backup('manual');
            session()->flash('message', 'Backup completed: '.$record->filename);
        } catch (\Throwable $e) {
            session()->flash('error', 'Backup failed: '.$e->getMessage());
        }

        $this->loading = false;
    }

    public function restore(string $id): void
    {
        $record = BackupRecord::findOrFail($id);
        $this->authorize('restore', $record);

        try {
            app(BackupService::class)->restore($record);
            session()->flash('message', 'Backup restored successfully.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Restore failed: '.$e->getMessage());
        }
    }

    public function render()
    {
        $googleDrive = app(GoogleDriveService::class);
        $driveFiles = $googleDrive->available() ? $googleDrive->listBackups() : [];
        $drivesAvailable = $googleDrive->available();

        return view('livewire.backups.index', [
            'records' => BackupRecord::orderByDesc('created_at')->paginate(10),
            'driveFiles' => $driveFiles,
            'drivesAvailable' => $drivesAvailable,
            'retention' => config('landlord.backup.retention'),
        ])->layout('layouts.app');
    }
}
