<?php

namespace App\Livewire\Electricity;

use App\Models\ElectricityBill;
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

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function finalize(string $id): void
    {
        $bill = ElectricityBill::findOrFail($id);
        $this->authorize('finalize', $bill);
        app(TariffService::class)->finalize($bill);
        session()->flash('message', 'Electricity bill finalized.');
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
        session()->flash('message', 'Electricity bill updated.');
    }

    public function delete(string $id): void
    {
        $bill = ElectricityBill::findOrFail($id);
        $this->authorize('delete', $bill);

        app(AuditService::class)->record('electricity_bill.deleted', 'ElectricityBill', $bill->id, null, $bill->toArray());
        $bill->delete();
        session()->flash('message', 'Electricity bill deleted.');
    }

    public function render()
    {
        $bills = ElectricityBill::query()
            ->when($this->month, fn ($q) => $q->where('billing_month', $this->month))
            ->when($this->propertyId, fn ($q) => $q->where('property_id', $this->propertyId))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->search, fn ($q) => $q->whereHas('tenant', fn ($q) =>
                $q->where('full_name', 'like', "%{$this->search}%"))
                ->orWhereHas('meter', fn ($q) => $q->where('meter_number', 'like', "%{$this->search}%")))
            ->with(['tenant', 'unit', 'property', 'meter', 'tariff'])
            ->orderByDesc('billing_month')
            ->paginate(12);

        return view('livewire.electricity.index', [
            'bills' => $bills,
            'properties' => Property::active()->orderBy('name')->get(),
            'months' => collect(range(0, 11))->map(fn ($i) => now()->subMonths($i)->format('Y-m')),
        ])->layout('layouts.app');
    }
}
