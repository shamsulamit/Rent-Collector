<?php

namespace App\Livewire\Properties;

use App\Enums\UnitStatus;
use App\Models\Floor;
use App\Models\Property;
use App\Models\Unit;
use App\Services\AuditService;
use Livewire\Component;
use Livewire\WithPagination;

class UnitsIndex extends Component
{
    use WithPagination;

    public Property $property;
    public string $search = '';
    public ?string $floorId = null;
    public string $status = '';

    public bool $showForm = false;
    public ?string $editingId = null;
    public array $form = [
        'floor_id' => '', 'name' => '', 'unit_type' => '', 'size' => '',
        'bedrooms' => 0, 'bathrooms' => 1, 'monthly_rent' => 0,
        'status' => 'vacant', 'notes' => '',
    ];

    protected $rules = [
        'form.name' => 'required|string|max:255',
        'form.monthly_rent' => 'required|numeric|min:0',
        'form.bedrooms' => 'nullable|integer|min:0',
        'form.bathrooms' => 'nullable|integer|min:0',
        'form.size' => 'nullable|numeric|min:0',
    ];

    public function mount(Property $property): void
    {
        $this->property = $property;
    }

    public function openCreate(): void
    {
        $this->reset('editingId');
        $this->form = [
            'floor_id' => $this->floorId ?? $this->property->floors()->first()?->id ?? '',
            'name' => '', 'unit_type' => '', 'size' => '',
            'bedrooms' => 0, 'bathrooms' => 1, 'monthly_rent' => 0,
            'status' => 'vacant', 'notes' => '',
        ];
        $this->showForm = true;
    }

    public function openEdit(string $id): void
    {
        $unit = Unit::findOrFail($id);
        $this->editingId = $id;
        $this->form = $unit->only(array_keys($this->form));
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $unit = Unit::findOrFail($this->editingId);
            $this->authorize('update', $unit);
            $before = $unit->toArray();
            $unit->update($this->form);
            app(AuditService::class)->changes('unit.updated', $unit, $before, $this->form);
            session()->flash('message', 'Unit updated.');
        } else {
            $this->authorize('create', Unit::class);
            $unit = $this->property->units()->create($this->form);
            app(AuditService::class)->record('unit.created', 'Unit', $unit->id, $unit->toArray());
            session()->flash('message', 'Unit created.');
        }

        $this->showForm = false;
        $this->reset('editingId');
    }

    public function delete(string $id): void
    {
        $unit = Unit::findOrFail($id);
        $this->authorize('delete', $unit);

        if ($unit->activeTenancy) {
            session()->flash('error', 'End the active tenancy before deleting this unit.');
            return;
        }

        app(AuditService::class)->record('unit.deleted', 'Unit', $unit->id, null, $unit->toArray());
        $unit->update(['is_deleted' => true, 'status' => 'inactive']);

        session()->flash('message', 'Unit deleted.');
    }

    public function render()
    {
        $units = Unit::query()
            ->where('property_id', $this->property->id)
            ->where('is_deleted', false)
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->floorId, fn ($q) => $q->where('floor_id', $this->floorId))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->with(['floor', 'activeTenancy.tenant', 'meters'])
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.properties.units', [
            'units' => $units,
            'floors' => $this->property->floors()->where('is_deleted', false)->orderBy('name')->get(),
            'statuses' => UnitStatus::cases(),
        ])->layout('layouts.app');
    }
}
