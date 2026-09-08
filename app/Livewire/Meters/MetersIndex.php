<?php

namespace App\Livewire\Meters;

use App\Models\Meter;
use App\Models\Property;
use App\Models\UtilityType;
use App\Services\AuditService;
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
        'unit_price' => '',
        'status' => 'active',
        'notes' => '',
    ];

    protected function rules(): array
    {
        return [
            'form.meter_number' => 'required|string|max:255|unique:meters,meter_number,'.($this->editingId ?: 'NULL').',id',
            'form.property_id' => 'required|exists:properties,id',
            'form.unit_id' => 'required|exists:units,id',
            'form.starting_reading' => 'nullable|numeric|min:0',
            'form.unit_price' => 'nullable|numeric|min:0',
            'form.installation_date' => 'nullable|date',
        ];
    }

    public function updatedFormPropertyId(): void
    {
        $this->form['unit_id'] = '';
    }

    public function openCreate(): void
    {
        $this->reset('editingId');
        $this->form = [
            'property_id' => $this->propertyId ?? '',
            'unit_id' => '',
            'meter_number' => '',
            'provider' => '',
            'meter_type' => 'postpaid',
            'utility' => $this->utility ?: 'electricity',
            'measurement_unit' => 'kWh',
            'installation_date' => now()->toDateString(),
            'starting_reading' => 0,
            'unit_price' => '',
            'status' => 'active',
            'notes' => '',
        ];
        $this->showForm = true;
    }

    public function openEdit(string $id): void
    {
        $meter = Meter::findOrFail($id);
        $this->editingId = $id;
        $this->form = $meter->only(array_keys($this->form));
        $this->form['installation_date'] = optional($meter->installation_date)->toDateString() ?? '';
        $this->form['unit_price'] = $meter->unit_price !== null ? (string) $meter->unit_price : '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $payload = $this->form;
        $unit = \App\Models\Unit::find($payload['unit_id']);
        $payload['floor_id'] = $unit?->floor_id;
        $payload['measurement_unit'] = $payload['measurement_unit'] ?: ($payload['utility'] === 'electricity' ? 'kWh' : 'm³');
        if (($payload['meter_type'] ?? '') !== 'prepaid' || ($payload['unit_price'] ?? '') === '') {
            $payload['unit_price'] = null;
        }

        if ($this->editingId) {
            $meter = Meter::findOrFail($this->editingId);
            $this->authorize('update', $meter);
            $meter->update($payload);
            app(AuditService::class)->record('meter.updated', 'Meter', $meter->id, $payload);
            notify('Meter saved.');
        } else {
            $this->authorize('create', Meter::class);
            $meter = Meter::create($payload);
            app(AuditService::class)->record('meter.created', 'Meter', $meter->id, $meter->toArray());
            notify('Meter added.');
        }

        $this->showForm = false;
        $this->reset('editingId');
    }

    public function delete(string $id): void
    {
        $meter = Meter::findOrFail($id);
        $this->authorize('delete', $meter);

        if ($meter->electricityBills()->whereIn('status', ['finalized', 'paid', 'partial', 'due', 'overpaid'])->exists()) {
            notify('This meter has finalized electricity bills and cannot be deleted.', 'error');
            return;
        }

        app(AuditService::class)->record('meter.deleted', 'Meter', $meter->id, null, $meter->toArray());
        $meter->update(['is_deleted' => true, 'status' => 'inactive']);

        notify('Meter deleted.');
    }

    public function render()
    {
        $meters = Meter::query()
            ->where('is_deleted', false)
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
                ? \App\Models\Unit::where('property_id', $this->form['property_id'])->where('is_deleted', false)->orderBy('name')->get()
                : collect(),
            'utilityTypes' => UtilityType::all(),
            'providers' => config('landlord.utilities.electricity_providers'),
        ])->layout('layouts.app');
    }
}
