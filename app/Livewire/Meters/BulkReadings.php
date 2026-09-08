<?php

namespace App\Livewire\Meters;

use App\Models\Meter;
use App\Models\MeterReading;
use App\Models\Property;
use App\Models\Tariff;
use App\Services\AuditService;
use App\Services\Electricity\TariffService;
use App\Services\Utilities\UtilityBillingService;
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
            ->where('is_deleted', false)
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

            $bill = $meter->electricityBills()->where('billing_month', $this->month)->first();

            $this->readings[$meter->id] = [
                'meter' => $meter,
                'previous' => (float) $previous,
                'current' => $existing?->current_reading ?? '',
                'status' => $existing?->status ?? 'draft',
                'note' => $existing?->notes ?? '',
                'anomaly' => 'normal',
                'charge' => $existing?->usage ?? null,
                'bill' => $bill,
            ];
        }
    }

    public function usageOf(string $meterId): float
    {
        $row = $this->readings[$meterId] ?? null;
        if (! $row || $row['current'] === '' || ! is_numeric($row['current'])) {
            return 0;
        }

        $current = (float) $row['current'];
        $previous = (float) $row['previous'];
        $anomaly = $row['anomaly'] ?? 'normal';

        if ($current < $previous && in_array($anomaly, ['reset', 'replacement'])) {
            return max(0, $current);
        }

        return max(0, $current - $previous);
    }

    public function chargeOf(string $meterId): float
    {
        if (! in_array($this->utility, ['electricity', 'gas', 'water'])) {
            return 0;
        }
        $row = $this->readings[$meterId] ?? null;
        if (! $row) {
            return 0;
        }
        $tariff = Tariff::query()->forDate(now()->toDateString(), $this->utility, $row['meter']->meter_type ?: 'postpaid')->first();

        return $tariff ? $tariff->calculateEnergy($this->usageOf($meterId)) : 0;
    }

    public function updatedReadingsCurrent(): void
    {
        foreach ($this->readings as $meterId => $row) {
            if (is_array($row) && isset($row['current']) && $row['current'] !== '' && is_numeric($row['current'])) {
                $prev = (float) ($row['previous'] ?? 0);
                $anomaly = $row['anomaly'] ?? 'normal';
                if ((float) $row['current'] < $prev && ! in_array($anomaly, ['reset', 'replacement', 'correction'])) {
                    $this->addError('readings.'.$meterId.'.current', 'Current reading is below the previous reading ('.$prev.'). Choose Reset, Replacement, or Correction.');
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
            $anomaly = $row['anomaly'] ?? 'normal';
            if ($current < $previous && ! in_array($anomaly, ['reset', 'replacement', 'correction'])) {
                continue;
            }
            $usage = $this->usageOf((string) $meterId);
            $notes = trim(($row['note'] ?? '').($anomaly !== 'normal' ? ' ['.$anomaly.']' : ''));

            if ($current < $previous && $anomaly === 'replacement') {
                $meter->update(['status' => 'replaced', 'notes' => trim(($meter->notes ?? '').' Replaced on '.$this->month)]);
            }

            MeterReading::updateOrCreate(
                ['meter_id' => $meter->id, 'billing_month' => $this->month],
                [
                    'property_id' => $meter->property_id,
                    'unit_id' => $meter->unit_id,
                    'tenant_id' => $meter->unit->activeTenancy?->tenant_id,
                    'tenancy_id' => $meter->unit->activeTenancy?->id,
                    'previous_reading' => $anomaly === 'reset' ? 0 : $previous,
                    'current_reading' => $current,
                    'usage' => $usage,
                    'reading_date' => now()->toDateString(),
                    'status' => $status,
                    'entered_by' => auth()->id(),
                    'notes' => $notes,
                ]
            );

            app(AuditService::class)->record('meter_reading.saved', 'MeterReading', $meter->id, [
                'month' => $this->month,
                'previous' => $previous,
                'current' => $current,
            ]);
        }
    }

    public function deleteElectricityBill(string $meterId): void
    {
        if ($this->utility !== 'electricity') {
            return;
        }

        $meter = Meter::findOrFail($meterId);
        $bill = $meter->electricityBills()->where('billing_month', $this->month)->first();

        if (! $bill) {
            session()->flash('message', 'No electricity bill exists for this meter and month.');
            return;
        }

        if ($bill->isImmutable() && ! auth()->user()?->isOwner()) {
            session()->flash('error', 'This electricity bill is finalized and cannot be deleted.');
            return;
        }

        $this->authorize('delete', $bill);

        app(AuditService::class)->record(
            'electricity_bill.deleted',
            'ElectricityBill',
            $bill->id,
            null,
            $bill->toArray()
        );

        $bill->delete();

        session()->flash('message', 'Electricity bill deleted for '.$meter->meter_number.'.');
        $this->loadRows();
    }

    public function calculateAll(): void
    {
        foreach ($this->readings as $meterId => $row) {
            if (! is_array($row) || $row['current'] === '') {
                continue;
            }
            $meter = Meter::find($meterId);
            if (! $meter || $meter->meter_type === 'prepaid') {
                continue;
            }
            $reading = $meter->readings()->where('billing_month', $this->month)->first();
            if (! $reading) {
                continue;
            }

            if ($this->utility === 'electricity') {
                app(TariffService::class)->calculate($meter, $reading);
            } elseif (in_array($this->utility, ['gas', 'water'])) {
                app(UtilityBillingService::class)->calculate($meter, $reading);
            }
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
