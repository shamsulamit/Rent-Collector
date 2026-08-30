<?php

namespace App\Livewire\Meters;

use App\Models\Meter;
use App\Models\MeterReading;
use App\Models\Property;
use App\Models\Tariff;
use App\Services\AuditService;
use App\Services\Electricity\TariffService;
use Livewire\Component;
use Livewire\WithPagination;

class BulkReadings extends Component
{
    use WithPagination;

    public string $month;
    public string $utility = 'electricity';
    public ?string $propertyId = null;
    public ?string $floorId = null;
    public ?string $unitId = null;
    public string $search = '';
    public string $filterStatus = '';

    /** @var array<int, array{current: string, status: string, note: string}> */
    public array $readings = [];

    protected $listeners = ['refreshReadings' => '$refresh'];

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
        $this->loadRows();
    }

    public function updatedMonth(): void
    {
        $this->loadRows();
    }

    public function updatedPropertyId(): void
    {
        $this->unitId = null;
        $this->loadRows();
    }

    public function updatedUtility(): void
    {
        $this->loadRows();
    }

    public function loadRows(): void
    {
        $meters = Meter::query()
            ->where('status', 'active')
            ->when($this->utility, fn ($q) => $q->where('utility', $this->utility))
            ->when($this->propertyId, fn ($q) => $q->where('property_id', $this->propertyId))
            ->when($this->floorId, fn ($q) => $q->where('floor_id', $this->floorId))
            ->when($this->unitId, fn ($q) => $q->where('unit_id', $this->unitId))
            ->when($this->search, fn ($q) => $q->whereHas('unit', fn ($q) =>
                $q->where('name', 'like', "%{$this->search}%"))
            )
            ->with(['unit.floor', 'unit.activeTenancy.tenant', 'property'])
            ->orderBy('meter_number')
            ->get();

        $this->readings = [];

        foreach ($meters as $meter) {
            $existing = $meter->readings()->where('billing_month', $this->month)->first();
            $previous = $existing?->previous_reading
                ?? $meter->lastReading($this->month)?->current_reading
                ?? $meter->starting_reading;

            $this->readings[$meter->id] = [
                'meter' => $meter,
                'previous' => (float) $previous,
                'current' => $existing?->current_reading ?? '',
                'status' => $existing?->status ?? 'draft',
                'note' => $existing?->notes ?? '',
                'charge' => $existing?->usage ?? null,
            ];
        }
    }

    public function usageOf(string $meterId): float
    {
        $row = $this->readings[$meterId] ?? null;
        if (! $row || $row['current'] === '' || ! is_numeric($row['current'])) {
            return 0;
        }

        return max(0, (float) $row['current'] - (float) $row['previous']);
    }

    public function chargeOf(string $meterId): float
    {
        if ($this->utility !== 'electricity') {
            return 0;
        }
        $row = $this->readings[$meterId] ?? null;
        if (! $row) {
            return 0;
        }
        $meter = $row['meter'];
        $tariff = Tariff::query()->forDate(now()->toDateString(), 'electricity', 'postpaid')->first();

        return $tariff ? $tariff->calculateEnergy($this->usageOf($meterId)) : 0;
    }

    public function updatedReadingsCurrent(): void
    {
        foreach ($this->readings as $meterId => $row) {
            if (is_array($row) && isset($row['current']) && $row['current'] !== '' && is_numeric($row['current'])) {
                $prev = (float) ($row['previous'] ?? 0);
                if ((float) $row['current'] < $prev) {
                    $this->addError('readings.'.$meterId.'.current', 'Current reading is below the previous reading ('.$prev.').');
                }
            }
        }
    }

    public function saveDraft(): void
    {
        $this->persist('draft');
        session()->flash('message', 'Draft readings saved.');
        $this->loadRows();
    }

    public function submitAll(): void
    {
        $this->persist('submitted');
        $this->calculateAll();
        session()->flash('message', 'Readings submitted and utility charges calculated.');
        $this->loadRows();
    }

    protected function persist(string $status): void
    {
        foreach ($this->readings as $meterId => $row) {
            if (! is_array($row) || $row['current'] === '' || ! is_numeric($row['current'])) {
                continue;
            }

            $meter = Meter::find($meterId);
            if (! $meter) {
                continue;
            }

            $this->authorize('create', MeterReading::class);

            $previous = (float) ($row['previous'] ?? 0);
            $current = (float) $row['current'];

            MeterReading::updateOrCreate(
                ['meter_id' => $meter->id, 'billing_month' => $this->month],
                [
                    'property_id' => $meter->property_id,
                    'unit_id' => $meter->unit_id,
                    'tenant_id' => $meter->unit->activeTenancy?->tenant_id,
                    'tenancy_id' => $meter->unit->activeTenancy?->id,
                    'previous_reading' => $previous,
                    'current_reading' => $current,
                    'usage' => max(0, $current - $previous),
                    'reading_date' => now()->toDateString(),
                    'status' => $status,
                    'entered_by' => auth()->id(),
                    'notes' => $row['note'] ?? '',
                ]
            );

            app(AuditService::class)->record('meter_reading.saved', 'MeterReading', $meter->id, [
                'month' => $this->month,
                'previous' => $previous,
                'current' => $current,
            ]);
        }
    }

    public function calculateAll(): void
    {
        if ($this->utility !== 'electricity') {
            return;
        }

        foreach ($this->readings as $meterId => $row) {
            if (! is_array($row) || $row['current'] === '') {
                continue;
            }
            $meter = Meter::find($meterId);
            $reading = $meter?->readings()->where('billing_month', $this->month)->first();
            if (! $reading) {
                continue;
            }
            app(TariffService::class)->calculate($meter, $reading);
        }
    }

    public function render()
    {
        $missing = collect($this->readings)->filter(fn ($row) => is_array($row) && ($row['current'] === '' || ! is_numeric($row['current'])))->count();

        return view('livewire.meters.bulk-readings', [
            'properties' => Property::active()->orderBy('name')->get(),
            'floors' => $this->propertyId
                ? \App\Models\Floor::where('property_id', $this->propertyId)->orderBy('name')->get()
                : collect(),
            'units' => $this->propertyId
                ? \App\Models\Unit::where('property_id', $this->propertyId)->orderBy('name')->get()
                : collect(),
            'missing' => $missing,
        ])->layout('layouts.app');
    }
}
