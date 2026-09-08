<?php

namespace App\Livewire\Utilities;

use App\Models\GasBill;
use App\Models\Property;
use App\Models\WaterBill;
use App\Services\AuditService;
use Livewire\Component;
use Livewire\WithPagination;

class UtilityBillsIndex extends Component
{
    use WithPagination;

    public string $utility = 'gas';
    public string $month = '';
    public ?string $propertyId = null;

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
        $this->utility = request('utility', 'gas') === 'water' ? 'water' : 'gas';
    }

    public function delete(string $id): void
    {
        $model = $this->utility === 'water' ? WaterBill::class : GasBill::class;
        $bill = $model::findOrFail($id);
        $this->authorize('manage readings');
        app(AuditService::class)->record($this->utility.'_bill.deleted', class_basename($bill), $bill->id, null, $bill->toArray());
        $bill->delete();
        session()->flash('message', ucfirst($this->utility).' bill deleted.');
    }

    public function render()
    {
        $model = $this->utility === 'water' ? WaterBill::class : GasBill::class;

        $bills = $model::query()
            ->when($this->month, fn ($q) => $q->where('billing_month', $this->month))
            ->when($this->propertyId, fn ($q) => $q->where('property_id', $this->propertyId))
            ->with(['meter', 'tenant', 'unit', 'meter.property'])
            ->orderByDesc('billing_month')
            ->paginate(12);

        return view('livewire.utilities.index', [
            'bills' => $bills,
            'properties' => Property::active()->orderBy('name')->get(),
            'months' => collect(range(0, 11))->map(fn ($i) => now()->subMonths($i)->format('Y-m')),
        ])->layout('layouts.app');
    }
}
