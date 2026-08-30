<?php

namespace App\Services\Backup;

use App\Models\BackupRecord;
use App\Services\AuditService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BackupService
{
    public function __construct(protected AuditService $audit) {}

    /**
     * Create a database backup: dump -> compress -> encrypt -> store.
     */
    public function backup(string $type = 'manual'): BackupRecord
    {
        $record = BackupRecord::create([
            'filename' => null,
            'disk' => 'local',
            'status' => 'running',
            'type' => $type,
            'is_encrypted' => (bool) config('landlord.backup.encrypt'),
            'started_at' => now(),
            'created_by' => auth()->id(),
        ]);

        try {
            $filename = 'backup-'.now()->format('Y-m-d-Hi').'-'.$record->id.'.sql';
            $path = $this->dump($filename);
            $size = filesize(storage_path('app/'.$path));

            if (config('landlord.backup.encrypt') && ($key = config('landlord.backup.encryption_key'))) {
                $this->encrypt(storage_path('app/'.$path), $key);
                $filename .= '.enc';
                $path = $this->dumpPath($filename);
            }

            $record->update([
                'filename' => $filename,
                'path' => $path,
                'size' => $size,
                'status' => 'success',
                'completed_at' => now(),
            ]);

            $this->audit->record('backup.created', 'BackupRecord', $record->id, $record->toArray());

            return $record;
        } catch (Throwable $e) {
            $record->update(['status' => 'failed', 'error' => substr($e->getMessage(), 0, 1000)]);
            $this->audit->record('backup.failed', 'BackupRecord', $record->id, ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    protected function dumpPath(string $filename): string
    {
        return 'backups/'.$filename;
    }

    protected function dump(string $filename): string
    {
        $dir = storage_path('app/backups');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $path = $this->dumpPath($filename);
        $full = storage_path('app/'.$path);

        $cmd = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s 2>&1',
            escapeshellarg(config('database.connections.mysql.host')),
            escapeshellarg(config('database.connections.mysql.port')),
            escapeshellarg(config('database.connections.mysql.username')),
            escapeshellarg(config('database.connections.mysql.password')),
            escapeshellarg(config('database.connections.mysql.database')),
            escapeshellarg($full)
        );

        exec($cmd, $output, $exitCode);
        if ($exitCode !== 0) {
            throw new \RuntimeException('mysqldump failed: '.implode("\n", $output));
        }

        return $path;
    }

    protected function encrypt(string $path, string $key): void
    {
        $data = file_get_contents($path);
        $iv = random_bytes(16);
        $cipher = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        file_put_contents($path.'.enc', base64_encode($iv)."\n".$cipher);
        @unlink($path);
    }

    /**
     * Restore from a backup record (Owner only, guarded by policy).
     */
    public function restore(BackupRecord $record): void
    {
        if ($record->status !== 'success') {
            throw new \DomainException('Cannot restore from a non-successful backup.');
        }

        $full = storage_path('app/'.$record->path);
        if (! file_exists($full)) {
            throw new \DomainException('Backup file not found.');
        }

        $data = file_get_contents($full);
        if ($record->is_encrypted && ($key = config('landlord.backup.encryption_key'))) {
            [$ivB64, $cipher] = explode("\n", $data, 2);
            $data = openssl_decrypt($cipher, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, base64_decode($ivB64));
        }

        $tmp = storage_path('app/backups/restore-'.$record->id.'.sql');
        file_put_contents($tmp, $data);

        $cmd = sprintf(
            'mysql --host=%s --port=%s --user=%s --password=%s %s < %s 2>&1',
            escapeshellarg(config('database.connections.mysql.host')),
            escapeshellarg(config('database.connections.mysql.port')),
            escapeshellarg(config('database.connections.mysql.username')),
            escapeshellarg(config('database.connections.mysql.password')),
            escapeshellarg(config('database.connections.mysql.database')),
            escapeshellarg($tmp)
        );

        exec($cmd, $output, $exitCode);
        @unlink($tmp);

        if ($exitCode !== 0) {
            throw new \RuntimeException('mysql restore failed: '.implode("\n", $output));
        }

        $record->update(['restored_at' => now()]);
        $this->audit->record('backup.restored', 'BackupRecord', $record->id, $record->toArray());
    }

    public function purgeOld(int $keep = 10): int
    {
        $old = BackupRecord::where('status', 'success')
            ->orderByDesc('created_at')
            ->get()
            ->slice($keep);

        $deleted = 0;
        foreach ($old as $record) {
            Storage::disk('local')->delete($record->path);
            $record->delete();
            $deleted++;
        }

        return $deleted;
    }
}
