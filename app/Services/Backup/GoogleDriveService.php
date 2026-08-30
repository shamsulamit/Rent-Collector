<?php

namespace App\Services\Backup;

use Google\Client as GoogleClient;
use Google\Service\Drive;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    protected ?Drive $drive = null;

    protected function client(): ?Drive
    {
        if ($this->drive) {
            return $this->drive;
        }

        $credentialsPath = config('landlord.backup.google_credentials');
        if (! $credentialsPath || ! file_exists($credentialsPath)) {
            return null;
        }

        try {
            $client = new GoogleClient();
            $client->setAuthConfig($credentialsPath);
            $client->setScopes([Drive::DRIVE_FILE]);
            $this->drive = new Drive($client);

            return $this->drive;
        } catch (\Throwable $e) {
            Log::warning('Google Drive client failed to initialise: '.$e->getMessage());

            return null;
        }
    }

    public function available(): bool
    {
        return $this->client() !== null;
    }

    public function folderId(): ?string
    {
        return config('landlord.backup.google_folder_id') ?: null;
    }

    /**
     * Upload a file to Google Drive. Returns file id or null when not configured.
     */
    public function upload(string $localPath, string $remoteName, ?string $folderId = null): ?string
    {
        $drive = $this->client();
        if (! $drive) {
            return null;
        }

        $fileMetadata = new Drive\DriveFile([
            'name' => $remoteName,
            'parents' => [$folderId ?: $this->folderId()],
        ]);

        $file = $drive->files->create(
            $fileMetadata,
            [
                'data' => file_get_contents($localPath),
                'mimeType' => 'application/octet-stream',
                'uploadType' => 'multipart',
                'fields' => 'id',
            ]
        );

        return $file->getId();
    }

    public function listBackups(int $limit = 10): array
    {
        $drive = $this->client();
        if (! $drive) {
            return [];
        }

        $files = $drive->files->listFiles([
            'q' => "'".$this->folderId()."' in parents and trashed = false",
            'orderBy' => 'createdTime desc',
            'pageSize' => $limit,
            'fields' => 'files(id,name,size,createdTime)',
        ]);

        return collect($files->getFiles())->map(fn ($f) => [
            'id' => $f->getId(),
            'name' => $f->getName(),
            'size' => (int) ($f->getSize() ?? 0),
            'created_at' => $f->getCreatedTime(),
        ])->all();
    }
}
