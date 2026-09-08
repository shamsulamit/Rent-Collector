<?php

namespace App\Livewire\Electricity;

use App\Models\ElectricityBill;
use App\Services\Electricity\TariffService;
use Livewire\Component;

class ElectricityBillShow extends Component
{
    public ElectricityBill $bill;

    public function mount(ElectricityBill $bill): void
    {
        $this->authorize('view', $bill);
        $this->bill = $bill->load(['tenant', 'unit.floor', 'property', 'meter', 'tariff', 'tenancy']);
    }

    public function finalize(): void
    {
        $this->authorize('finalize', $this->bill);
        app(TariffService::class)->finalize($this->bill);
        $this->bill->refresh();
        session()->flash('message', 'Electricity bill finalized. Historical tariff is locked.');
    }

    public function render()
    {
        return view('livewire.electricity.show')->layout('layouts.app');
    }
}
