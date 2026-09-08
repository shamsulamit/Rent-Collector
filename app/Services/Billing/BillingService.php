<?php

namespace App\Services\Billing;

use App\Models\Bill;
use App\Models\ElectricityBill;
use App\Models\GasBill;
use App\Models\Setting;
use App\Models\Tenancy;
use App\Models\Unit;
use App\Models\WaterBill;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function __construct(protected AuditService $audit) {}

    /**
     * Generate (or update draft) monthly bills for all active tenancies.
     */
    public function generateMonthlyBills(string $billingMonth, ?array $unitIds = null): array
    {
        [$year, $month] = array_map('intval', explode('-', $billingMonth));
        $results = ['created' => 0, 'updated' => 0, 'skipped' => 0];

        $tenancies = Tenancy::query()
            ->with(['tenant', 'unit.property', 'unit.meters'])
            ->where('status', 'active')
            ->when($unitIds, fn ($q) => $q->whereIn('unit_id', $unitIds))
            ->get();

        foreach ($tenancies as $tenancy) {
            $existing = Bill::where('tenant_id', $tenancy->tenant_id)
                ->where('unit_id', $tenancy->unit_id)
                ->where('billing_month', $billingMonth)
                ->first();

            if ($existing && $existing->isImmutable()) {
                $results['skipped']++;

                continue;
            }

            $data = $this->computeBill($tenancy, $year, $month, $billingMonth);

            if ($existing) {
                $existing->update($data);
                $results['updated']++;
            } else {
                $data['bill_no'] = Bill::query()->count() + 1;
                $data['status'] = 'draft';
                $bill = Bill::create($data);
                $this->syncItems($bill);
                $results['created']++;
            }
        }

        $this->audit->record('billing.generated', 'Bill', null, $results);

        return $results;
    }

    /**
     * Compute a single month's bill breakdown for a tenancy.
     */
    public function computeBill(Tenancy $tenancy, int $year, int $month, string $billingMonth): array
    {
        $monthStart = Carbon::create($year, $month, 1)->startOfDay();
        $daysInMonth = $monthStart->daysInMonth;

        $rent = $this->computeRent($tenancy, $monthStart, $daysInMonth);
        $electricity = $this->utilityTotal($tenancy, $billingMonth, ElectricityBill::class);
        $gas = $this->utilityTotal($tenancy, $billingMonth, GasBill::class);
        $water = $this->utilityTotal($tenancy, $billingMonth, WaterBill::class);

        $recurring = Setting::get('recurring_charges', '[]');
        $recurring = is_array($recurring) ? $recurring : json_decode($recurring, true) ?? [];
        $charges = array_fill_keys(['waste', 'security', 'cleaning', 'internet', 'parking', 'other'], 0);

        foreach ($recurring as $key => $value) {
            if (array_key_exists($key, $charges)) {
                $charges[$key] = (float) $value;
            }
        }

        $total = $rent + $electricity + $gas + $water + array_sum($charges);

        return [
            'property_id' => $tenancy->property_id,
            'unit_id' => $tenancy->unit_id,
            'tenancy_id' => $tenancy->id,
            'tenant_id' => $tenancy->tenant_id,
            'billing_month' => $billingMonth,
            'rent' => round($rent, 2),
            'electricity' => round($electricity, 2),
            'gas' => round($gas, 2),
            'water' => round($water, 2),
            'waste' => round($charges['waste'], 2),
            'security' => round($charges['security'], 2),
            'cleaning' => round($charges['cleaning'], 2),
            'internet' => round($charges['internet'], 2),
            'parking' => round($charges['parking'], 2),
            'other' => round($charges['other'], 2),
            'discount' => 0,
            'adjustment' => 0,
            'total' => round($total, 2),
            'status' => 'draft',
            'generated_by' => auth()->id(),
        ];
    }

    /**
     * Rent for the month, considering move-in proration and rent change effective dates.
     */
    public function computeRent(Tenancy $tenancy, Carbon $monthStart, int $daysInMonth): float
    {
        $baseRent = (float) $tenancy->monthly_rent;

        $lastChange = $tenancy->rentChanges()
            ->where('effective_date', '<=', $monthStart->copy()->endOfMonth()->toDateString())
            ->orderByDesc('effective_date')
            ->first();

        if ($lastChange) {
            $baseRent = (float) $lastChange->new_rent;
        }

        $prorateDays = $daysInMonth;

        if ($tenancy->move_in_date && $tenancy->move_in_date->isSameMonth($monthStart, false)) {
            $prorateDays = $daysInMonth - $tenancy->move_in_date->day + 1;
        } elseif ($tenancy->move_in_date && $tenancy->move_in_date->isAfter($monthStart->copy()->endOfMonth())) {
            $prorateDays = 0;
        }

        if ($tenancy->move_out_date && $tenancy->move_out_date->isSameMonth($monthStart, false)) {
            $prorateDays = min($prorateDays, $tenancy->move_out_date->day);
        }

        if ($lastChange && $lastChange->effective_date->isSameMonth($monthStart, false)) {
            $changeDay = $lastChange->effective_date->day;
            $oldDays = $changeDay - 1;
            $newDays = $daysInMonth - $changeDay + 1;
            $oldRent = (float) $lastChange->old_rent;

            return round(
                ($oldRent / $daysInMonth * $oldDays) + ($baseRent / $daysInMonth * $newDays),
                2
            );
        }

        return round($baseRent / $daysInMonth * $prorateDays, 2);
    }

    protected function utilityTotal(Tenancy $tenancy, string $billingMonth, string $model): float
    {
        $query = $model::query()
            ->where('billing_month', $billingMonth)
            ->where('unit_id', $tenancy->unit_id);

        $field = $model === GasBill::class || $model === WaterBill::class ? 'charge' : 'total';

        return (float) $query->whereIn('status', ['finalized', 'calculated', 'paid', 'partial', 'due'])->sum($field);
    }

    /**
     * Persist line items for a bill from its breakdown columns.
     */
    public function syncItems(Bill $bill): void
    {
        $bill->items()->delete();

        $map = [
            'rent' => 'Rent',
            'electricity' => 'Electricity',
            'gas' => 'Gas',
            'water' => 'Water',
            'waste' => 'Waste',
            'security' => 'Security',
            'cleaning' => 'Cleaning',
            'internet' => 'Internet',
            'parking' => 'Parking',
            'other' => 'Other',
        ];

        $sort = 0;
        foreach ($map as $key => $label) {
            if ((float) $bill->{$key} > 0) {
                $bill->items()->create([
                    'type' => $key,
                    'label' => $label,
                    'amount' => (float) $bill->{$key},
                    'sort' => $sort++,
                ]);
            }
        }
        if ((float) $bill->discount > 0) {
            $bill->items()->create([
                'type' => 'discount',
                'label' => 'Discount',
                'amount' => -1 * (float) $bill->discount,
                'sort' => $sort++,
            ]);
        }
        if ((float) $bill->adjustment != 0) {
            $bill->items()->create([
                'type' => 'adjustment',
                'label' => 'Adjustment',
                'amount' => (float) $bill->adjustment,
                'sort' => $sort++,
            ]);
        }
    }

    /**
     * Finalize a draft/calculated bill. Immutable afterwards.
     */
    public function finalize(Bill $bill): Bill
    {
        if ($bill->isImmutable()) {
            throw new \DomainException('This bill is already finalized and cannot be modified.');
        }

        return DB::transaction(function () use ($bill) {
            $bill->update([
                'status' => 'finalized',
                'finalized_at' => now()->toDateString(),
                'finalized_by' => auth()->id(),
            ]);

            $this->audit->record('bill.finalized', 'Bill', $bill->id, $bill->toArray());

            return $bill;
        });
    }

    public function delete(Bill $bill): void
    {
        DB::transaction(function () use ($bill) {
            $this->audit->record('bill.deleted', 'Bill', $bill->id, null, $bill->toArray());
            $bill->items()->delete();
            $bill->allocations()->delete();
            $bill->delete();
        });
    }

    public function adjust(Bill $bill, float $discount, float $adjustment): Bill
    {
        if ($bill->isImmutable() && ! auth()->user()?->isOwner()) {
            throw new \DomainException('This bill is finalized and cannot be modified.');
        }

        $subtotal = (float) $bill->rent + (float) $bill->electricity + (float) $bill->gas + (float) $bill->water
            + (float) $bill->waste + (float) $bill->security + (float) $bill->cleaning
            + (float) $bill->internet + (float) $bill->parking + (float) $bill->other;

        $bill->update([
            'discount' => $discount,
            'adjustment' => $adjustment,
            'total' => round($subtotal - $discount + $adjustment, 2),
        ]);
        $this->syncItems($bill);

        return $bill->fresh();
    }
}
