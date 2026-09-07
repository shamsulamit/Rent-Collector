<?php

namespace App\Livewire\Properties;

use App\Models\Property;
use App\Services\AuditService;
use Livewire\Component;
use Livewire\WithPagination;

class PropertiesIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';

    public bool $showForm = false;
    public ?string $editingId = null;
    public array $form = [
        'name' => '',
        'address' => '',
        'city' => '',
        'area' => '',
        'postal_code' => '',
        'contact_phone' => '',
        'contact_email' => '',
        'description' => '',
        'status' => 'active',
        'notes' => '',
    ];

    protected $rules = [
        'form.name' => 'required|string|max:255',
        'form.city' => 'nullable|string|max:255',
        'form.postal_code' => 'nullable|string|max:20',
        'form.contact_email' => 'nullable|email',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset('editingId', 'form');
        $this->form['status'] = 'active';
        $this->showForm = true;
    }

    public function openEdit(string $id): void
    {
        $property = Property::findOrFail($id);
        $this->editingId = $id;
        $this->form = $property->only(array_keys($this->form));
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $property = Property::findOrFail($this->editingId);
            $this->authorize('update', $property);
            $before = $property->toArray();
            $property->update($this->form);
            app(AuditService::class)->changes('property.updated', $property, $before, $this->form);
            session()->flash('message', 'Property updated.');
        } else {
            $this->authorize('create', Property::class);
            $property = Property::create($this->form);
            app(AuditService::class)->record('property.created', 'Property', $property->id, $property->toArray());
            session()->flash('message', 'Property created.');
        }

        $this->showForm = false;
        $this->reset('editingId');
    }

    public function delete(string $id): void
    {
        $property = Property::findOrFail($id);
        $this->authorize('delete', $property);

        app(AuditService::class)->record('property.deleted', 'Property', $property->id, null, $property->toArray());
        $property->update(['is_deleted' => true, 'status' => 'inactive']);

        session()->flash('message', 'Property deleted.');
    }

    public function render()
    {
        $properties = Property::query()
            ->where('is_deleted', false)
            ->when($this->search, fn ($q) => $q->where(fn ($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('city', 'like', "%{$this->search}%")
                    ->orWhere('area', 'like', "%{$this->search}%"))
            )
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->withCount(['units', 'tenancies'])
            ->withCount(['units as occupied_units_count' => fn ($q) => $q->where('status', 'occupied')])
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.properties.index', compact('properties'))
            ->layout('layouts.app');
    }
}
