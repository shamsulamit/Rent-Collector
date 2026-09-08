<?php

namespace App\Livewire\Vendors;

use App\Models\Vendor;
use App\Services\AuditService;
use Livewire\Component;
use Livewire\WithPagination;

class VendorsIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showForm = false;
    public ?string $editingId = null;
    public array $form = [
        'name' => '',
        'phone' => '',
        'email' => '',
        'address' => '',
        'category' => 'maintenance',
        'notes' => '',
    ];

    protected $rules = [
        'form.name' => 'required|string|max:255',
        'form.email' => 'nullable|email',
        'form.phone' => 'nullable|string|max:30',
    ];

    public function openCreate(): void
    {
        $this->reset('editingId', 'form');
        $this->showForm = true;
    }

    public function openEdit(string $id): void
    {
        $vendor = Vendor::findOrFail($id);
        $this->authorize('update', $vendor);
        $this->editingId = $id;
        $this->form = $vendor->only(array_keys($this->form));
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $vendor = Vendor::findOrFail($this->editingId);
            $this->authorize('update', $vendor);
            $vendor->update($this->form);
            app(AuditService::class)->record('vendor.updated', 'Vendor', $vendor->id, $this->form);
            session()->flash('message', 'Vendor updated.');
        } else {
            $this->authorize('create', Vendor::class);
            $vendor = Vendor::create($this->form);
            app(AuditService::class)->record('vendor.created', 'Vendor', $vendor->id, $vendor->toArray());
            session()->flash('message', 'Vendor created.');
        }

        $this->showForm = false;
        $this->reset('editingId');
    }

    public function delete(string $id): void
    {
        $vendor = Vendor::findOrFail($id);
        $this->authorize('delete', $vendor);
        $vendor->update(['is_deleted' => true]);
        app(AuditService::class)->record('vendor.deleted', 'Vendor', $vendor->id, null, $vendor->toArray());
        session()->flash('message', 'Vendor deleted.');
    }

    public function render()
    {
        $this->authorize('viewAny', Vendor::class);

        $vendors = Vendor::query()
            ->where('is_deleted', false)
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.vendors.index', compact('vendors'))->layout('layouts.app');
    }
}
