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
    public bool $showRent = false;
    public ?string $editingId = null;
    public ?string $rentTenancyId = null;
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

    public array $rentForm = [
        'new_rent' => '',
        'effective_date' => '',
        'proration_method' => 'calendar',
        'reason' => '',
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
        $this->reset('form', 'lease', 'editingId');
        $this->form['move_in_date'] = now()->toDateString();
        $this->form['deposit'] = '0';
        $this->lease = [
            'start_date' => '',
            'end_date' => '',
            'security_deposit' => '',
            'terms' => '',
            'status' => 'active',
        ];
        $this->showForm = true;
    }

    public function openEdit(string $id): void
    {
        $tenancy = Tenancy::with('leases')->findOrFail($id);
        $this->authorize('update', $tenancy);
        $this->editingId = $id;
        $this->form = [
            'tenant_id' => $tenancy->tenant_id,
            'property_id' => $tenancy->property_id,
            'unit_id' => $tenancy->unit_id,
            'move_in_date' => optional($tenancy->move_in_date)->toDateString(),
            'monthly_rent' => (string) $tenancy->monthly_rent,
            'deposit' => (string) $tenancy->deposit,
            'notes' => $tenancy->notes ?? '',
        ];
        $lease = $tenancy->leases->first();
        $this->lease = [
            'start_date' => optional($lease?->start_date)->toDateString() ?? '',
            'end_date' => optional($lease?->end_date)->toDateString() ?? '',
            'security_deposit' => (string) ($lease?->security_deposit ?? ''),
            'terms' => $lease?->terms ?? '',
            'status' => $lease?->status ?? 'active',
        ];
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $unit = Unit::findOrFail($this->form['unit_id']);
        $payload = $this->tenancyPayload();
        $leasePayload = $this->leasePayload();

        if ($this->editingId) {
            $tenancy = Tenancy::findOrFail($this->editingId);
            $this->authorize('update', $tenancy);
            $tenancy->update($payload);
            if ($this->shouldSaveLease()) {
                $tenancy->leases()->updateOrCreate(
                    ['tenancy_id' => $tenancy->id],
                    $leasePayload + [
                        'tenant_id' => $tenancy->tenant_id,
                        'unit_id' => $tenancy->unit_id,
                        'monthly_rent' => $tenancy->monthly_rent,
                    ]
                );
            }
            app(AuditService::class)->record('tenancy.updated', 'Tenancy', $tenancy->id, $tenancy->toArray());
            session()->flash('message', 'Tenancy updated.');
        } else {
            $tenancy = Tenancy::create($payload + ['status' => 'active']);
            $unit->markOccupied(Tenant::find($this->form['tenant_id'])?->full_name);

            if ($this->shouldSaveLease()) {
                $tenancy->leases()->create($leasePayload + [
                    'tenant_id' => $tenancy->tenant_id,
                    'unit_id' => $tenancy->unit_id,
                    'monthly_rent' => $tenancy->monthly_rent,
                ]);
            }

            app(AuditService::class)->record('tenancy.created', 'Tenancy', $tenancy->id, $tenancy->toArray());
            session()->flash('message', 'Tenancy created.');
        }

        $this->showForm = false;
        $this->reset('editingId');
    }

    public function openRentChange(string $id): void
    {
        $tenancy = Tenancy::findOrFail($id);
        $this->authorize('update', $tenancy);
        $this->rentTenancyId = $id;
        $this->rentForm = [
            'new_rent' => (string) $tenancy->monthly_rent,
            'effective_date' => now()->toDateString(),
            'proration_method' => 'calendar',
            'reason' => '',
        ];
        $this->showRent = true;
    }

    public function saveRentChange(): void
    {
        $this->validate([
            'rentForm.new_rent' => 'required|numeric|min:0',
            'rentForm.effective_date' => 'required|date',
        ]);

        $tenancy = Tenancy::findOrFail($this->rentTenancyId);
        $this->authorize('update', $tenancy);

        $tenancy->rentChanges()->create([
            'unit_id' => $tenancy->unit_id,
            'tenant_id' => $tenancy->tenant_id,
            'user_id' => auth()->id(),
            'old_rent' => $tenancy->monthly_rent,
            'new_rent' => $this->rentForm['new_rent'],
            'effective_date' => $this->rentForm['effective_date'],
            'proration_method' => $this->rentForm['proration_method'],
            'reason' => $this->rentForm['reason'],
        ]);

        $tenancy->update(['monthly_rent' => $this->rentForm['new_rent']]);
        $tenancy->unit?->update(['monthly_rent' => $this->rentForm['new_rent']]);

        app(AuditService::class)->record('rent.changed', 'Tenancy', $tenancy->id, $this->rentForm);
        $this->showRent = false;
        session()->flash('message', 'Rent change recorded. Historical bills stay unchanged.');
    }

    public function delete(string $id): void
    {
        $tenancy = Tenancy::findOrFail($id);
        $this->authorize('delete', $tenancy);
        $wasActive = $tenancy->status === 'active';
        $tenancy->update(['is_deleted' => true, 'status' => 'ended']);
        if ($wasActive) {
            $tenancy->unit?->markVacant();
        }
        app(AuditService::class)->record('tenancy.deleted', 'Tenancy', $tenancy->id, null, $tenancy->toArray());
        session()->flash('message', 'Tenancy deleted.');
    }

    protected function tenancyPayload(): array
    {
        return [
            'tenant_id' => $this->form['tenant_id'],
            'property_id' => $this->form['property_id'],
            'unit_id' => $this->form['unit_id'],
            'move_in_date' => $this->form['move_in_date'] ?: null,
            'monthly_rent' => $this->form['monthly_rent'] === '' ? 0 : $this->form['monthly_rent'],
            'deposit' => $this->form['deposit'] === '' ? 0 : $this->form['deposit'],
            'notes' => $this->form['notes'] ?: null,
        ];
    }

    protected function leasePayload(): array
    {
        return [
            'start_date' => $this->lease['start_date'] ?: null,
            'end_date' => $this->lease['end_date'] ?: null,
            'security_deposit' => $this->lease['security_deposit'] === '' ? 0 : $this->lease['security_deposit'],
            'terms' => $this->lease['terms'] ?: null,
            'status' => $this->lease['status'] ?: 'active',
        ];
    }

    protected function shouldSaveLease(): bool
    {
        return filled($this->lease['start_date']) || filled($this->lease['end_date']) || filled($this->lease['terms']);
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
            ->where('is_deleted', false)
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
                ->when(! $this->editingId, fn ($q) => $q->whereIn('status', ['vacant', 'reserved']))
                ->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
