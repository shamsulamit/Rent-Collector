<?php

namespace App\Livewire\Tenants;

use App\Models\Bill;
use App\Models\Document;
use App\Models\Property;
use App\Models\SecurityDeposit;
use App\Models\Tenant;
use App\Models\Unit;
use App\Services\AuditService;
use App\Services\Payments\PaymentService;
use Livewire\Component;
use Livewire\WithFileUploads;

class TenantShow extends Component
{
    use WithFileUploads;

    public Tenant $tenant;
    public array $timeline = [];

    public $document;
    public string $docType = 'nid';

    public bool $showMoveIn = false;
    public array $moveIn = [
        'property_id' => '',
        'unit_id' => '',
        'move_in_date' => '',
        'monthly_rent' => '',
        'deposit' => '',
    ];

    public bool $showDeposit = false;
    public array $deposit = [
        'amount' => '',
        'type' => 'received',
        'date' => '',
        'method' => '',
        'reference' => '',
        'notes' => '',
    ];

    protected $rules = [
        'moveIn.property_id' => 'required|exists:properties,id',
        'moveIn.unit_id' => 'required|exists:units,id',
        'moveIn.move_in_date' => 'required|date',
        'moveIn.monthly_rent' => 'required|numeric|min:0',
        'deposit.amount' => 'required|numeric',
        'deposit.date' => 'required|date',
    ];

    public function mount(Tenant $tenant): void
    {
        $this->tenant = $tenant->load(['tenancies.unit.property', 'bills.unit', 'payments', 'securityDeposits', 'documents']);
        $this->timeline = $tenant->timeline()->map(fn ($e) => array_merge($e, [
            'icon' => match ($e['type']) {
                'move_in' => 'key',
                'move_out' => 'logout',
                'bill' => 'doc',
                'payment' => 'cash',
                'deposit' => 'vault',
                default => 'dot',
            },
        ]))->toArray();
    }

    public function updatedMoveInPropertyId(): void
    {
        $this->moveIn['unit_id'] = '';
    }

    public function openMoveIn(): void
    {
        $this->reset('moveIn');
        $this->moveIn['move_in_date'] = now()->toDateString();
        $this->showMoveIn = true;
    }

    public function saveMoveIn(): void
    {
        $this->validate();

        $unit = Unit::findOrFail($this->moveIn['unit_id']);
        $this->authorize('create', Tenant::class);

        $tenancy = $this->tenant->tenancies()->create([
            'property_id' => $this->moveIn['property_id'],
            'unit_id' => $unit->id,
            'move_in_date' => $this->moveIn['move_in_date'],
            'monthly_rent' => $this->moveIn['monthly_rent'],
            'deposit' => $this->moveIn['deposit'] ?? 0,
            'status' => 'active',
        ]);

        $unit->markOccupied($this->tenant->full_name);

        app(AuditService::class)->record('tenancy.created', 'Tenancy', $tenancy->id, $tenancy->toArray());
        session()->flash('message', 'Tenancy started.');
        $this->showMoveIn = false;
        $this->mount($this->tenant->fresh());
    }

    public function openDeposit(): void
    {
        $this->reset('deposit');
        $this->deposit['date'] = now()->toDateString();
        $this->showDeposit = true;
    }

    public function saveDeposit(): void
    {
        $this->validate();

        $this->tenant->securityDeposits()->create($this->deposit + [
            'tenancy_id' => $this->tenant->activeTenancy?->id,
            'recorded_by' => auth()->id(),
        ]);

        app(AuditService::class)->record('deposit.recorded', 'SecurityDeposit', $this->tenant->id, $this->deposit);
        session()->flash('message', 'Deposit recorded.');
        $this->showDeposit = false;
        $this->mount($this->tenant->fresh());
    }

    public function moveOut(string $tenancyId): void
    {
        $tenancy = $this->tenant->tenancies()->findOrFail($tenancyId);
        $tenancy->moveOut();
        app(AuditService::class)->record('tenancy.ended', 'Tenancy', $tenancy->id, $tenancy->toArray());
        session()->flash('message', 'Tenancy ended. History preserved.');
        $this->mount($this->tenant->fresh());
    }

    public function uploadDocument(): void
    {
        $this->authorize('create', Document::class);
        $this->validate([
            'document' => ['required', 'file', 'max:10240'],
            'docType' => ['required', 'string'],
        ]);

        $doc = Document::store('tenant', $this->tenant->id, $this->document, $this->docType, $this->document->getClientOriginalName());
        app(AuditService::class)->record('document.uploaded', 'Document', $doc->id, $doc->toArray());
        session()->flash('message', 'Document uploaded.');
        $this->mount($this->tenant->fresh());
    }

    public function render()
    {
        $paymentService = app(PaymentService::class);

        return view('livewire.tenants.show', [
            'properties' => Property::active()->orderBy('name')->get(),
            'units' => Unit::where('property_id', $this->moveIn['property_id'])
                ->whereIn('status', ['vacant', 'reserved'])
                ->orderBy('name')->get(),
            'outstanding' => $paymentService->tenantOutstanding($this->tenant),
            'credit' => $paymentService->tenantCredit($this->tenant),
            'depositBalance' => $this->tenant->depositBalance(),
            'docTypes' => ['nid', 'passport', 'lease', 'agreement', 'utility_bill', 'receipt', 'other'],
        ])->layout('layouts.app');
    }
}
