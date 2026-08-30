<?php

namespace App\Services\Electricity;

use App\Models\ElectricityBill;
use App\Models\Meter;
use App\Models\MeterReading;
use App\Models\Tariff;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

class TariffService
{
    public function __construct(protected AuditService $audit) {}

    /**
     * Calculate a postpaid electricity bill for a meter + reading + month.
     */
    public function calculate(Meter $meter, MeterReading $reading, ?Tariff $tariff = null): ElectricityBill
    {
        $tariff = $tariff ?? Tariff::query()
            ->forDate(($reading->reading_date ?? now()->toDateString()), 'electricity', 'postpaid')
            ->first();

        $usage = max(0, (float) $reading->current_reading - (float) $reading->previous_reading);
        $energy = $tariff ? $tariff->calculateEnergy($usage) : 0;
        $fixed = $tariff?->fixed_charge ?? 0;
        $service = $tariff?->service_charge ?? 0;
        $demand = $tariff?->demand_charge ?? 0;
        $other = $tariff?->other_charge ?? 0;
        $vatRate = $tariff?->vat_rate ?? 0;

        $subtotal = $energy + $fixed + $service + $demand + $other;
        $vat = round($subtotal * ($vatRate / 100), 2);
        $total = round($subtotal + $vat, 2);

        return DB::transaction(function () use ($meter, $reading, $tariff, $usage, $energy, $fixed, $service, $demand, $vat, $other, $total) {
            $bill = ElectricityBill::updateOrCreate(
                ['meter_id' => $meter->id, 'billing_month' => $reading->billing_month],
                [
                    'property_id' => $meter->property_id,
                    'unit_id' => $meter->unit_id,
                    'tenant_id' => $reading->tenant_id,
                    'tenancy_id' => $reading->tenancy_id,
                    'tariff_id' => $tariff?->id,
                    'billing_month' => $reading->billing_month,
                    'previous_reading' => $reading->previous_reading,
                    'current_reading' => $reading->current_reading,
                    'usage' => $usage,
                    'energy_charge' => $energy,
                    'fixed_charge' => $fixed,
                    'service_charge' => $service,
                    'demand_charge' => $demand,
                    'vat' => $vat,
                    'other_charge' => $other,
                    'total' => $total,
                    'status' => 'calculated',
                ]
            );

            $reading->update([
                'usage' => $usage,
                'status' => 'submitted',
            ]);

            $this->audit->record('electricity_bill.calculated', $bill->id, null, $bill->toArray());

            return $bill;
        });
    }

    /**
     * Finalize a calculated electricity bill. Immutable afterwards.
     */
    public function finalize(ElectricityBill $bill): ElectricityBill
    {
        if ($bill->isImmutable()) {
            throw new \DomainException('This electricity bill is already finalized and cannot be modified.');
        }

        return DB::transaction(function () use ($bill) {
            $bill->update([
                'status' => 'finalized',
                'finalized_at' => now()->toDateString(),
                'finalized_by' => auth()->id(),
            ]);

            $this->audit->record('electricity_bill.finalized', $bill->id, null, $bill->toArray());

            return $bill;
        });
    }
}
