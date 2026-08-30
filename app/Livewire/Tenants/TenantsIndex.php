<?php

namespace App\Livewire\Tenants;

use App\Models\Tenant;
use App\Services\AuditService;
use Livewire\Component;
use Livewire\WithPagination;

class TenantsIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showForm = false;
    public ?string $editingId = null;
    public array $form = [
        'full_name' => '',
        'bangla_name' => '',
        'nid' => '',
        'passport' => '',
        'phone' => '',
        'whatsapp_number' => '',
        'email' => '',
        'address' => '',
        'emergency_contact' => '',
        'notes' => '',
    ];

    protected $rules = [
        'form.full_name' => 'required|string|max:255',
        'form.email' => 'nullable|email',
        'form.nid' => 'nullable|string|max:50',
        'form.phone' => 'nullable|string|max:30',
        'form.whatsapp_number' => 'nullable|string|max:30',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset('editingId', 'form');
        $this->showForm = true;
    }

    public function openEdit(string $id): void
    {
        $tenant = Tenant::findOrFail($id);
        $this->editingId = $id;
        $this->form = $tenant->only(array_keys($this->form));
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $tenant = Tenant::findOrFail($this->editingId);
            $this->authorize('update', $tenant);
            $before = $tenant->toArray();
            $tenant->update($this->form);
            app(AuditService::class)->changes('tenant.updated', $tenant, $before, $this->form);
            session()->flash('message', 'Tenant updated.');
        } else {
            $this->authorize('create', Tenant::class);
            $tenant = Tenant::create($this->form);
            app(AuditService::class)->record('tenant.created', 'Tenant', $tenant->id, $tenant->toArray());
            session()->flash('message', 'Tenant created.');
        }

        $this->showForm = false;
        $this->reset('editingId');
    }

    public function render()
    {
        $tenants = Tenant::query()
            ->where('is_deleted', false)
            ->when($this->search, fn ($q) => $q->where(fn ($q) =>
                $q->where('full_name', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%")
                    ->orWhere('nid', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%"))
            )
            ->with(['activeTenancy.unit.property'])
            ->orderBy('full_name')
            ->paginate(12);

        return view('livewire.tenants.index', compact('tenants'))
            ->layout('layouts.app');
    }
}
