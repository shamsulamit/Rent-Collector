<?php

namespace App\Services\Utilities;

use App\Models\GasBill;
use App\Models\Meter;
use App\Models\MeterReading;
use App\Models\Setting;
use App\Models\Tariff;
use App\Models\WaterBill;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

class UtilityBillingService
{
    public function __construct(protected AuditService $audit) {}

    public function calculate(Meter $meter, MeterReading $reading): GasBill|WaterBill
    {
        $utility = $meter->utility === 'water' ? 'water' : 'gas';
        $tariff = Tariff::query()
            ->forDate($reading->reading_date?->toDateString() ?? now()->toDateString(), $utility, $meter->meter_type ?: 'postpaid')
            ->first();

        $usage = max(0, (float) $reading->current_reading - (float) $reading->previous_reading);
        $rate = $this->unitRate($tariff, $utility);
        $energy = $tariff ? $tariff->calculateEnergy($usage) : round($usage * $rate, 2);
        if ($energy <= 0 && $rate > 0) {
            $energy = round($usage * $rate, 2);
        }

        $fixed = (float) ($tariff?->fixed_charge ?? 0);
        $service = (float) ($tariff?->service_charge ?? 0);
        $charge = round($energy + $fixed + $service, 2);

        $payload = [
            'property_id' => $meter->property_id,
            'unit_id' => $meter->unit_id,
            'tenant_id' => $reading->tenant_id,
            'tenancy_id' => $reading->tenancy_id,
            'billing_month' => $reading->billing_month,
            'previous_reading' => $reading->previous_reading,
            'current_reading' => $reading->current_reading,
            'usage' => $usage,
            'rate' => $rate,
            'charge' => $charge,
            'status' => 'calculated',
        ];

        return DB::transaction(function () use ($meter, $reading, $utility, $payload, $charge) {
            $model = $utility === 'water' ? WaterBill::class : GasBill::class;
            $bill = $model::updateOrCreate(
                ['meter_id' => $meter->id, 'billing_month' => $reading->billing_month],
                $payload
            );

            $reading->update(['usage' => $payload['usage'], 'status' => 'submitted']);
            $this->audit->record($utility.'_bill.calculated', class_basename($bill), $bill->id, ['charge' => $charge]);

            return $bill;
        });
    }

    protected function unitRate(?Tariff $tariff, string $utility): float
    {
        if ($tariff && ! empty($tariff->slabs)) {
            $first = collect($tariff->slabs)->sortBy('min')->first();

            return (float) ($first['rate'] ?? 0);
        }

        $key = $utility === 'water' ? 'water_unit_rate' : 'gas_unit_rate';

        return (float) Setting::get($key, 0);
    }
}
