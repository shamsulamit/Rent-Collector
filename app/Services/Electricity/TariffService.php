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

    public function resolveTariff(Meter $meter, $date, string $utility = 'electricity'): ?Tariff
    {
        $date = $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : (string) ($date ?: now()->toDateString());
        $meterType = $meter->meter_type ?: 'postpaid';

        $query = Tariff::query()
            ->forDate($date, $utility, $meterType)
            ->where('is_active', true);

        if ($meter->provider) {
            $query->orderByRaw('CASE WHEN provider = ? THEN 0 ELSE 1 END', [$meter->provider]);
        }

        return $query->orderByDesc('effective_date')->first();
    }

    /**
     * Calculate a postpaid electricity bill for a meter + reading + month.
     * Finalized / paid bills are never recalculated (historical tariff lock).
     */
    public function calculate(Meter $meter, MeterReading $reading, ?Tariff $tariff = null): ElectricityBill
    {
        if (($meter->meter_type ?: 'postpaid') === 'prepaid') {
            throw new \DomainException('Prepaid meters use the recharge workflow, not postpaid monthly invoices.');
        }

        $meter->loadMissing(['unit.activeTenancy']);

        $existing = ElectricityBill::query()
            ->where('meter_id', $meter->id)
            ->where('billing_month', $reading->billing_month)
            ->first();

        if ($existing && $existing->isImmutable()) {
            return $existing;
        }

        $date = $reading->reading_date
            ?? (! empty($reading->billing_month) ? $reading->billing_month.'-01' : now()->toDateString());

        $tariff = $tariff ?? $this->resolveTariff($meter, $date, 'electricity');

        $usage = max(0, (float) $reading->current_reading - (float) $reading->previous_reading);
        $energy = $tariff ? $tariff->calculateEnergy($usage) : 0;
        $fixed = (float) ($tariff?->fixed_charge ?? 0);
        $service = (float) ($tariff?->service_charge ?? 0);
        $demand = (float) ($tariff?->demand_charge ?? 0);
        $other = (float) ($tariff?->other_charge ?? 0);
        $vatRate = (float) ($tariff?->vat_rate ?? 0);

        $subtotal = $energy + $fixed + $service + $demand + $other;
        $vat = round($subtotal * ($vatRate / 100), 2);
        $discount = (float) ($existing?->discount ?? 0);
        $adjustment = (float) ($existing?->adjustment ?? 0);
        $total = round($subtotal + $vat - $discount + $adjustment, 2);

        return DB::transaction(function () use ($meter, $reading, $tariff, $usage, $energy, $fixed, $service, $demand, $vat, $other, $discount, $adjustment, $total) {
            $tenancy = $meter->unit?->activeTenancy;

            $bill = ElectricityBill::updateOrCreate(
                ['meter_id' => $meter->id, 'billing_month' => $reading->billing_month],
                [
                    'property_id' => $meter->property_id,
                    'unit_id' => $meter->unit_id,
                    'tenant_id' => $reading->tenant_id ?? $tenancy?->tenant_id,
                    'tenancy_id' => $reading->tenancy_id ?? $tenancy?->id,
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
                    'discount' => $discount,
                    'adjustment' => $adjustment,
                    'total' => $total,
                    'status' => 'calculated',
                ]
            );

            $reading->update([
                'usage' => $usage,
                'status' => 'submitted',
            ]);

            $this->audit->record('electricity_bill.calculated', 'ElectricityBill', $bill->id, $bill->toArray());

            return $bill;
        });
    }

    public function preview(Meter $meter, float $usage, $date = null): array
    {
        $tariff = $this->resolveTariff($meter, $date ?? now()->toDateString(), $meter->utility ?: 'electricity');
        $energy = $tariff ? $tariff->calculateEnergy($usage) : 0;
        $fixed = (float) ($tariff?->fixed_charge ?? 0);
        $service = (float) ($tariff?->service_charge ?? 0);
        $demand = (float) ($tariff?->demand_charge ?? 0);
        $other = (float) ($tariff?->other_charge ?? 0);
        $vatRate = (float) ($tariff?->vat_rate ?? 0);
        $subtotal = $energy + $fixed + $service + $demand + $other;
        $vat = round($subtotal * ($vatRate / 100), 2);

        return [
            'energy' => $energy,
            'fixed' => $fixed,
            'service' => $service,
            'demand' => $demand,
            'other' => $other,
            'vat' => $vat,
            'total' => round($subtotal + $vat, 2),
            'tariff' => $tariff,
        ];
    }

    public function recordTotal(Meter $meter, string $billingMonth, float $total): ElectricityBill
    {
        $meter->loadMissing(['unit.activeTenancy']);
        $tenancy = $meter->unit?->activeTenancy;
        $total = round($total, 2);

        $existing = ElectricityBill::query()
            ->where('meter_id', $meter->id)
            ->where('billing_month', $billingMonth)
            ->first();

        if ($existing && $existing->isImmutable() && ! auth()->user()?->isOwner()) {
            throw new \DomainException('This electricity bill is finalized and cannot be modified.');
        }

        return DB::transaction(function () use ($meter, $tenancy, $billingMonth, $total, $existing) {
            $bill = ElectricityBill::updateOrCreate(
                ['meter_id' => $meter->id, 'billing_month' => $billingMonth],
                [
                    'property_id' => $meter->property_id,
                    'unit_id' => $meter->unit_id,
                    'tenant_id' => $tenancy?->tenant_id,
                    'tenancy_id' => $tenancy?->id,
                    'previous_reading' => $existing?->previous_reading ?? 0,
                    'current_reading' => $existing?->current_reading ?? 0,
                    'usage' => $existing?->usage ?? 0,
                    'energy_charge' => $total,
                    'fixed_charge' => 0,
                    'service_charge' => 0,
                    'demand_charge' => 0,
                    'vat' => 0,
                    'other_charge' => 0,
                    'discount' => 0,
                    'adjustment' => 0,
                    'total' => $total,
                    'status' => 'calculated',
                ]
            );

            $this->audit->record('electricity_bill.recorded', 'ElectricityBill', $bill->id, ['total' => $total]);

            return $bill;
        });
    }

    public function adjust(ElectricityBill $bill, float $discount, float $adjustment): ElectricityBill
    {
        if ($bill->isImmutable() && ! auth()->user()?->isOwner()) {
            throw new \DomainException('This electricity bill is finalized and cannot be modified.');
        }

        $subtotal = (float) $bill->energy_charge + (float) $bill->fixed_charge + (float) $bill->service_charge
            + (float) $bill->demand_charge + (float) $bill->other_charge + (float) $bill->vat;

        $bill->update([
            'discount' => $discount,
            'adjustment' => $adjustment,
            'total' => round($subtotal - $discount + $adjustment, 2),
        ]);

        $this->audit->record('electricity_bill.adjusted', 'ElectricityBill', $bill->id, $bill->toArray());

        return $bill->fresh();
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

            $this->audit->record('electricity_bill.finalized', 'ElectricityBill', $bill->id, $bill->toArray());

            return $bill;
        });
    }
}
