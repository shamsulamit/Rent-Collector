<?php

namespace App\Livewire\Audit;

use App\Models\AuditLog;
use Livewire\Component;
use Livewire\WithPagination;

class AuditIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $action = '';

    public function render()
    {
        abort_unless(auth()->user()->isOwner() || auth()->user()->can('view audit logs'), 403);

        $logs = AuditLog::query()
            ->when($this->search, fn ($q) => $q->where('action', 'like', "%{$this->search}%")
                ->orWhere('entity_type', 'like', "%{$this->search}%")
                ->orWhere('entity_id', 'like', "%{$this->search}%"))
            ->when($this->action, fn ($q) => $q->where('action', 'like', $this->action.'%'))
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('livewire.audit.index', compact('logs'))->layout('layouts.app');
    }
}
