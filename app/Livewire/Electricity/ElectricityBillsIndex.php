<?php

namespace App\Livewire\Electricity;

use App\Models\ElectricityBill;
use App\Models\Meter;
use App\Models\Property;
use App\Services\AuditService;
use App\Services\Electricity\TariffService;
use Livewire\Component;
use Livewire\WithPagination;

class ElectricityBillsIndex extends Component
{
    use WithPagination;

    public string $month = '';
    public ?string $propertyId = null;
    public string $status = '';
    public string $search = '';

    public bool $showAdjust = false;
    public ?string $adjustingId = null;
    public array $adjust = ['discount' => '0', 'adjustment' => '0'];

    public bool $showCreate = false;
    public array $create = [
        'meter_id' => '',
        'billing_month' => '',
        'total' => '',
    ];

    public function mount(): void
    {
        $this->month = request('month', '');
    }

    public function finalize(string $id): void
    {
        $bill = ElectricityBill::findOrFail($id);
        $this->authorize('finalize', $bill);
        app(TariffService::class)->finalize($bill);
        notify('Electricity bill finalized.');
    }

    public function openAdjust(string $id): void
    {
        $bill = ElectricityBill::findOrFail($id);
        $this->authorize('update', $bill);
        $this->adjustingId = $id;
        $this->adjust = [
            'discount' => (string) $bill->discount,
            'adjustment' => (string) $bill->adjustment,
        ];
        $this->showAdjust = true;
    }

    public function saveAdjust(): void
    {
        $bill = ElectricityBill::findOrFail($this->adjustingId);
        $this->authorize('update', $bill);
        app(TariffService::class)->adjust(
            $bill,
            (float) $this->adjust['discount'],
            (float) $this->adjust['adjustment']
        );
        $this->showAdjust = false;
        notify('Electricity bill saved.');
    }

    public function openCreate(): void
    {
        $this->create = [
            'meter_id' => '',
            'billing_month' => now()->format('Y-m'),
            'total' => '',
        ];
        $this->showCreate = true;
    }

    public function saveCreate(): void
    {
        $this->create['billing_month'] = substr((string) $this->create['billing_month'], 0, 7);
        $this->validate([
            'create.meter_id' => 'required|exists:meters,id',
            'create.billing_month' => 'required|date_format:Y-m',
            'create.total' => 'required|numeric|min:0',
        ]);

        $meter = Meter::findOrFail($this->create['meter_id']);
        $this->authorize('create', ElectricityBill::class);

        try {
            app(TariffService::class)->recordTotal(
                $meter,
                $this->create['billing_month'],
                (float) $this->create['total']
            );
        } catch (\DomainException $e) {
            notify($e->getMessage(), 'error');

            return;
        }

        $this->showCreate = false;
        notify('Electricity bill added.');
    }

    public function delete(string $id): void
    {
        $bill = ElectricityBill::findOrFail($id);
        $this->authorize('delete', $bill);

        app(AuditService::class)->record('electricity_bill.deleted', 'ElectricityBill', $bill->id, null, $bill->toArray());
        $bill->delete();
        notify('Electricity bill deleted.');
    }

    public function render()
    {
        $bills = ElectricityBill::query()
            ->when($this->month, fn ($q) => $q->where('billing_month', $this->month))
            ->when($this->propertyId, fn ($q) => $q->where('property_id', $this->propertyId))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->whereHas('tenant', fn ($q) => $q->where('full_name', 'like', "%{$this->search}%"))
                        ->orWhereHas('meter', fn ($q) => $q->where('meter_number', 'like', "%{$this->search}%"));
                });
            })
            ->with(['tenant', 'unit', 'property', 'meter', 'tariff'])
            ->orderByDesc('billing_month')
            ->paginate(12);

        return view('livewire.electricity.index', [
            'bills' => $bills,
            'properties' => Property::active()->orderBy('name')->get(),
            'months' => collect(range(0, 11))->map(fn ($i) => now()->subMonths($i)->format('Y-m')),
            'postpaidMeters' => Meter::query()
                ->where('is_deleted', false)
                ->where('utility', 'electricity')
                ->where(fn ($q) => $q->whereNull('meter_type')->orWhere('meter_type', 'postpaid'))
                ->with('unit.property')
                ->orderBy('meter_number')
                ->get(),
        ])->layout('layouts.app');
    }
}
