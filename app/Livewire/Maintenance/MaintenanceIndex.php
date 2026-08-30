<?php

namespace App\Livewire\Maintenance;

use App\Models\MaintenanceTicket;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\Vendor;
use App\Services\AuditService;
use Livewire\Component;
use Livewire\WithPagination;

class MaintenanceIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $priority = '';

    public bool $showForm = false;
    public ?string $editingId = null;
    public array $form = [
        'property_id' => '',
        'unit_id' => '',
        'tenant_id' => '',
        'vendor_id' => '',
        'priority' => 'normal',
        'title' => '',
        'description' => '',
        'estimated_cost' => '',
        'actual_cost' => '',
        'opened_at' => '',
        'notes' => '',
    ];

    protected $rules = [
        'form.title' => 'required|string|max:255',
        'form.priority' => 'required|in:low,normal,high,urgent',
        'form.opened_at' => 'required|date',
        'form.estimated_cost' => 'nullable|numeric|min:0',
        'form.actual_cost' => 'nullable|numeric|min:0',
    ];

    public function mount(): void
    {
        $this->form['opened_at'] = now()->toDateString();
    }

    public function openCreate(): void
    {
        $this->reset('editingId');
        $this->form['opened_at'] = now()->toDateString();
        $this->showForm = true;
    }

    public function openEdit(string $id): void
    {
        $ticket = MaintenanceTicket::findOrFail($id);
        $this->editingId = $id;
        $this->form = $ticket->only(array_keys($this->form));
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $ticket = MaintenanceTicket::findOrFail($this->editingId);
            $this->authorize('update', $ticket);
            $ticket->update($this->form);
            app(AuditService::class)->record('maintenance.updated', 'MaintenanceTicket', $ticket->id, $this->form);
            session()->flash('message', 'Ticket updated.');
        } else {
            $this->authorize('create', MaintenanceTicket::class);
            $ticket = MaintenanceTicket::create($this->form + [
                'ticket_no' => MaintenanceTicket::nextTicketNumber(),
                'assigned_to' => auth()->id(),
            ]);
            app(AuditService::class)->record('maintenance.created', 'MaintenanceTicket', $ticket->id, $ticket->toArray());
            session()->flash('message', "Ticket {$ticket->ticket_no} created.");
        }

        $this->showForm = false;
        $this->reset('editingId');
    }

    public function setStatus(string $id, string $status): void
    {
        $ticket = MaintenanceTicket::findOrFail($id);
        $this->authorize('update', $ticket);

        $data = ['status' => $status];
        if ($status === 'completed') {
            $data['completed_at'] = now()->toDateString();
        }
        if ($status === 'cancelled') {
            $data['cancelled_at'] = now()->toDateString();
        }

        $ticket->update($data);
        app(AuditService::class)->record('maintenance.status', 'MaintenanceTicket', $ticket->id, $data);
        session()->flash('message', 'Ticket status updated.');
    }

    public function render()
    {
        $tickets = MaintenanceTicket::query()
            ->when($this->search, fn ($q) => $q->where(fn ($q) =>
                $q->where('ticket_no', 'like', "%{$this->search}%")
                    ->orWhere('title', 'like', "%{$this->search}%"))
            )
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->priority, fn ($q) => $q->where('priority', $this->priority))
            ->with(['property', 'unit', 'tenant', 'vendor'])
            ->orderByDesc('opened_at')
            ->paginate(12);

        return view('livewire.maintenance.index', [
            'tickets' => $tickets,
            'properties' => Property::active()->orderBy('name')->get(),
            'tenants' => Tenant::where('is_deleted', false)->orderBy('full_name')->get(),
            'vendors' => Vendor::where('is_deleted', false)->orderBy('name')->get(),
            'units' => $this->form['property_id']
                ? Unit::where('property_id', $this->form['property_id'])->orderBy('name')->get()
                : collect(),
        ])->layout('layouts.app');
    }
}
