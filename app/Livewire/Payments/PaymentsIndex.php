<?php

namespace App\Livewire\Payments;

use App\Models\Payment;
use App\Models\Property;
use App\Models\Tenant;
use App\Services\Payments\PaymentService;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentsIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $propertyId = null;
    public string $method = '';
    public string $from = '';
    public string $to = '';

    public bool $showForm = false;
    public array $form = [
        'tenant_id' => '',
        'property_id' => '',
        'unit_id' => '',
        'amount' => '',
        'payment_date' => '',
        'method' => 'cash',
        'reference' => '',
        'notes' => '',
    ];

    public string $strategy = 'oldest-first';

    protected $rules = [
        'form.tenant_id' => 'required|exists:tenants,id',
        'form.amount' => 'required|numeric|min:0.01',
        'form.payment_date' => 'required|date',
        'form.method' => 'required|string',
    ];

    public function mount(): void
    {
        $this->form['payment_date'] = now()->toDateString();
    }

    public function openCreate(): void
    {
        $this->reset('form');
        $this->form['payment_date'] = now()->toDateString();
        $this->showForm = true;
    }

    public function updatedFormTenantId(): void
    {
        $tenant = Tenant::with(['activeTenancy.unit.property'])->find($this->form['tenant_id']);
        if ($tenant?->activeTenancy) {
            $this->form['property_id'] = $tenant->activeTenancy->property_id;
            $this->form['unit_id'] = $tenant->activeTenancy->unit_id;
            $this->form['tenancy_id'] = $tenant->activeTenancy->id;
        }
    }

    public function save(): void
    {
        $this->validate();

        app(PaymentService::class)->record($this->form + [
            'recorded_by' => auth()->id(),
        ], $this->strategy);

        session()->flash('message', 'Payment recorded and allocated using "'.$this->strategy.'".');
        $this->showForm = false;
    }

    public function reallocate(string $id): void
    {
        $payment = Payment::findOrFail($id);
        $this->authorize('allocate', $payment);
        $payment->allocations()->delete();
        app(PaymentService::class)->allocate($payment, $this->strategy);
        session()->flash('message', 'Payment re-allocated.');
    }

    public function render()
    {
        $payments = Payment::query()
            ->when($this->search, fn ($q) => $q->whereHas('tenant', fn ($q) =>
                $q->where('full_name', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%"))
                ->orWhere('reference', 'like', "%{$this->search}%"))
            ->when($this->propertyId, fn ($q) => $q->where('property_id', $this->propertyId))
            ->when($this->method, fn ($q) => $q->where('method', $this->method))
            ->when($this->from, fn ($q) => $q->whereDate('payment_date', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('payment_date', '<=', $this->to))
            ->with(['tenant', 'unit', 'property', 'allocations.bill', 'recorder'])
            ->orderByDesc('payment_date')
            ->paginate(12);

        $payments->map(fn (Payment $p) => $p->unallocated_amount = $p->unallocated());

        return view('livewire.payments.index', [
            'payments' => $payments,
            'tenants' => Tenant::where('is_deleted', false)->orderBy('full_name')->get(),
            'properties' => Property::active()->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
