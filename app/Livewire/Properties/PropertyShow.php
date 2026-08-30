<?php

namespace App\Livewire\Properties;

use App\Models\Property;
use App\Services\AuditService;
use Livewire\Component;

class PropertyShow extends Component
{
    public Property $property;

    public function mount(Property $property): void
    {
        $this->property = $property->load([
            'floors' => fn ($q) => $q->withCount('units'),
            'units' => fn ($q) => $q->with('floor', 'activeTenancy.tenant'),
            'tenancies' => fn ($q) => $q->with('tenant', 'unit')->latest(),
            'meters' => fn ($q) => $q->with('unit'),
        ]);
    }

    public function render()
    {
        $occupied = $this->property->units->where('status', 'occupied')->count();
        $vacant = $this->property->units->where('status', 'vacant')->count();
        $month = now()->format('Y-m');

        return view('livewire.properties.show', [
            'occupied' => $occupied,
            'vacant' => $vacant,
            'month' => $month,
        ])->layout('layouts.app');
    }
}
