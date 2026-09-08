<?php

namespace App\Livewire\Bills;

use App\Models\Bill;
use App\Models\Property;
use App\Models\Setting;
use App\Services\Billing\BillingService;
use App\Services\Payments\PaymentService;
use App\Services\WhatsApp\WhatsAppService;
use Livewire\Component;
use Livewire\WithPagination;

class BillsIndex extends Component
{
    use WithPagination;

    public string $month;
    public ?string $propertyId = null;
    public ?string $unitId = null;
    public string $status = '';
    public string $search = '';

    public bool $showPayment = false;
    public ?string $billId = null;
    public array $payment = [
        'amount' => '',
        'payment_date' => '',
        'method' => 'cash',
        'reference' => '',
        'notes' => '',
    ];

    protected $rules = [
        'payment.amount' => 'required|numeric|min:1',
        'payment.payment_date' => 'required|date',
        'payment.method' => 'required|string',
    ];

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function updatedPropertyId(): void
    {
        $this->unitId = null;
    }

    public function generateBills(): void
    {
        $this->authorize('generate', Bill::class);
        $results = app(BillingService::class)->generateMonthlyBills($this->month);
        notify("Bills generated: {$results['created']} created, {$results['updated']} updated.");
        $this->loadBills();
    }

    public function finalize(string $id): void
    {
        $bill = Bill::findOrFail($id);
        $this->authorize('finalize', $bill);
        app(BillingService::class)->finalize($bill);
        notify('Bill finalized. It is now immutable.');
    }

    public function openPayment(string $id): void
    {
        $bill = Bill::findOrFail($id);
        $this->authorize('recordPayment', $bill);
        $this->billId = $id;
        $this->payment = [
            'amount' => (string) max(0, $bill->balance()),
            'payment_date' => now()->toDateString(),
            'method' => 'cash',
            'reference' => '',
            'notes' => '',
        ];
        $this->showPayment = true;
    }

    public function recordPayment(): void
    {
        $this->validate();
        $bill = Bill::findOrFail($this->billId);

        $payment = app(PaymentService::class)->record([
            'tenant_id' => $bill->tenant_id,
            'tenancy_id' => $bill->tenancy_id,
            'property_id' => $bill->property_id,
            'unit_id' => $bill->unit_id,
            'amount' => $this->payment['amount'],
            'payment_date' => $this->payment['payment_date'],
            'method' => $this->payment['method'],
            'reference' => $this->payment['reference'],
            'notes' => $this->payment['notes'],
            'recorded_by' => auth()->id(),
        ], 'oldest-first');

        notify('Payment recorded and allocated.');
        $this->showPayment = false;
    }

    public function delete(string $id): void
    {
        $bill = Bill::findOrFail($id);
        $this->authorize('delete', $bill);
        app(BillingService::class)->delete($bill);
        notify('Bill deleted.');
    }

    public function sendWhatsApp(string $id, string $template = 'monthly_bill'): void
    {
        $bill = Bill::findOrFail($id);
        app(WhatsAppService::class)->sendBill($template, $bill, Setting::get('whatsapp_locale', 'en'));
        notify('WhatsApp message created.');
    }

    public function loadBills(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $bills = Bill::query()
            ->when($this->month, fn ($q) => $q->where('billing_month', $this->month))
            ->when($this->propertyId, fn ($q) => $q->where('property_id', $this->propertyId))
            ->when($this->unitId, fn ($q) => $q->where('unit_id', $this->unitId))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->search, fn ($q) => $q->whereHas('tenant', fn ($q) =>
                $q->where('full_name', 'like', "%{$this->search}%"))
                ->orWhere('bill_no', 'like', "%{$this->search}%"))
            ->with(['tenant', 'unit.floor', 'property'])
            ->withSum('allocations as allocated_sum', 'amount')
            ->orderByDesc('billing_month')
            ->paginate(12);

        $bills->map(fn (Bill $b) => $b->paid = $b->totalPaid());

        return view('livewire.bills.index', [
            'bills' => $bills,
            'properties' => Property::active()->orderBy('name')->get(),
            'months' => collect(range(0, 11))->map(fn ($i) => now()->subMonths($i)->format('Y-m')),
        ])->layout('layouts.app');
    }
}
