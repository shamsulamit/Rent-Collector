<?php

namespace App\Livewire\Meters;

use App\Models\Meter;
use App\Models\Property;
use App\Models\Tariff;
use App\Models\UtilityType;
use App\Services\AuditService;
use App\Services\Electricity\TariffService;
use Livewire\Component;
use Livewire\WithPagination;

class MetersIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $propertyId = null;
    public string $utility = '';
    public string $status = 'active';

    public bool $showForm = false;
    public ?string $editingId = null;
    public array $form = [
        'property_id' => '',
        'unit_id' => '',
        'meter_number' => '',
        'provider' => '',
        'meter_type' => 'postpaid',
        'utility' => 'electricity',
        'measurement_unit' => 'kWh',
        'installation_date' => '',
        'starting_reading' => 0,
        'status' => 'active',
        'notes' => '',
    ];

    protected $rules = [
        'form.meter_number' => 'required|string|max:255',
        'form.property_id' => 'required|exists:properties,id',
        'form.unit_id' => 'required|exists:units,id',
        'form.starting_reading' => 'nullable|numeric|min:0',
    ];

    public array $readings = [];
    public string $readingMonth = '';

    public function updatedFormPropertyId(): void
    {
        $this->form['unit_id'] = '';
    }

    public function openCreate(): void
    {
        $this->reset('editingId', 'form');
        $this->form['installation_date'] = now()->toDateString();
        $this->showForm = true;
    }

    public function openEdit(string $id): void
    {
        $meter = Meter::findOrFail($id);
        $this->editingId = $id;
        $this->form = $meter->only(array_keys($this->form));
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $meter = Meter::findOrFail($this->editingId);
            $this->authorize('update', $meter);
            $meter->update($this->form);
            app(AuditService::class)->record('meter.updated', 'Meter', $meter->id, $this->form);
            session()->flash('message', 'Meter updated.');
        } else {
            $this->authorize('create', Meter::class);
            $meter = Meter::create($this->form);
            app(AuditService::class)->record('meter.created', 'Meter', $meter->id, $meter->toArray());
            session()->flash('message', 'Meter created.');
        }

        $this->showForm = false;
        $this->reset('editingId');
    }

    public function render()
    {
        $meters = Meter::query()
            ->when($this->search, fn ($q) => $q->where('meter_number', 'like', "%{$this->search}%"))
            ->when($this->propertyId, fn ($q) => $q->where('property_id', $this->propertyId))
            ->when($this->utility, fn ($q) => $q->where('utility', $this->utility))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->with(['unit.floor', 'property', 'utilityType'])
            ->withCount('readings')
            ->orderBy('meter_number')
            ->paginate(12);

        return view('livewire.meters.index', [
            'meters' => $meters,
            'properties' => Property::active()->orderBy('name')->get(),
            'units' => $this->form['property_id']
                ? \App\Models\Unit::where('property_id', $this->form['property_id'])->orderBy('name')->get()
                : collect(),
            'utilityTypes' => UtilityType::all(),
            'providers' => config('landlord.utilities.electricity_providers'),
        ])->layout('layouts.app');
    }
}
