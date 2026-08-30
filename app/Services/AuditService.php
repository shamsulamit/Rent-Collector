<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Record an audit event.
     */
    public function record(
        string $action,
        ?string $entityType = null,
        ?string $entityId = null,
        ?array $newData = null,
        ?array $oldData = null,
    ): AuditLog {
        return AuditLog::log($action, $entityType, $entityId, $oldData, $newData);
    }

    /**
     * Record a model change with before/after snapshots.
     */
    public function changes(string $action, $model, ?array $before = null, ?array $after = null): AuditLog
    {
        return $this->record(
            $action,
            class_basename($model),
            $model->id,
            $after ?? $model->getDirty(),
            $before
        );
    }

    public function login(): void
    {
        $this->record('auth.login', 'User', Auth::id());
    }

    public function logout(): void
    {
        $this->record('auth.logout', 'User', Auth::id());
    }
}
