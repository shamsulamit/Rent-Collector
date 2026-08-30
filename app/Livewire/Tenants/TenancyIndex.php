<?php

namespace App\Livewire\Tenants;

use App\Models\Lease;
use App\Models\Property;
use App\Models\Tenancy;
use App\Models\Tenant;
use App\Models\Unit;
use App\Services\AuditService;
use Livewire\Component;
use Livewire\WithPagination;

class TenancyIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $propertyId = null;
    public string $status = 'active';

    public bool $showForm = false;
    public array $form = [
        'tenant_id' => '',
        'property_id' => '',
        'unit_id' => '',
        'move_in_date' => '',
        'monthly_rent' => '',
        'deposit' => '',
        'notes' => '',
    ];

    public array $lease = [
        'start_date' => '',
        'end_date' => '',
        'security_deposit' => '',
        'terms' => '',
        'status' => 'active',
    ];

    protected $rules = [
        'form.tenant_id' => 'required|exists:tenants,id',
        'form.property_id' => 'required|exists:properties,id',
        'form.unit_id' => 'required|exists:units,id',
        'form.move_in_date' => 'required|date',
        'form.monthly_rent' => 'required|numeric|min:0',
    ];

    public function updatedFormPropertyId(): void
    {
        $this->form['unit_id'] = '';
    }

    public function openCreate(): void
    {
        $this->reset('form', 'lease');
        $this->form['move_in_date'] = now()->toDateString();
        $this->lease['start_date'] = now()->toDateString();
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $unit = Unit::findOrFail($this->form['unit_id']);

        $tenancy = Tenancy::create($this->form + ['status' => 'active']);
        $unit->markOccupied(Tenant::find($this->form['tenant_id'])?->full_name);

        if ($this->lease['start_date']) {
            $tenancy->leases()->create($this->lease + [
                'tenant_id' => $tenancy->tenant_id,
                'unit_id' => $tenancy->unit_id,
            ]);
        }

        app(AuditService::class)->record('tenancy.created', 'Tenancy', $tenancy->id, $tenancy->toArray());
        session()->flash('message', 'Tenancy created.');
        $this->showForm = false;
    }

    public function endTenancy(string $id): void
    {
        $tenancy = Tenancy::findOrFail($id);
        $tenancy->moveOut();
        app(AuditService::class)->record('tenancy.ended', 'Tenancy', $tenancy->id, $tenancy->toArray());
        session()->flash('message', 'Tenancy ended, history preserved.');
    }

    public function render()
    {
        $tenancies = Tenancy::query()
            ->when($this->search, fn ($q) => $q->whereHas('tenant', fn ($q) =>
                $q->where('full_name', 'like', "%{$this->search}%"))
            )
            ->when($this->propertyId, fn ($q) => $q->where('property_id', $this->propertyId))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->with(['tenant', 'unit.floor', 'property', 'leases'])
            ->orderByDesc('move_in_date')
            ->paginate(12);

        return view('livewire.tenants.tenancies', [
            'tenancies' => $tenancies,
            'tenants' => Tenant::where('is_deleted', false)->orderBy('full_name')->get(),
            'properties' => Property::active()->orderBy('name')->get(),
            'units' => Unit::where('property_id', $this->form['property_id'])
                ->whereIn('status', ['vacant', 'reserved'])
                ->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
