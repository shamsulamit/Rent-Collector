<?php

namespace App\Services\Reports;

use App\Models\Bill;
use App\Models\Expense;
use App\Models\Unit;
use Carbon\Carbon;

class ReportService
{
    /**
     * Dashboard aggregate stats.
     */
    public function dashboard(?string $propertyId = null, ?string $month = null, ?int $year = null): array
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->format('Y-m');

        $billQuery = Bill::query()
            ->whereIn('status', ['finalized', 'calculated', 'paid', 'partial', 'due', 'overpaid'])
            ->where('billing_month', $month)
            ->when($propertyId, fn ($q) => $q->where('property_id', $propertyId));

        $bills = $billQuery->withSum('allocations as allocated_sum', 'amount')->get();
        $expectedRent = (float) $bills->sum('rent');
        $collected = (float) $bills->sum(fn ($b) => $b->totalPaid());
        $due = (float) $bills->sum(fn ($b) => max(0, $b->balance()));

        $utilityBilled = (float) $bills->sum(fn ($b) => $b->electricity + $b->gas + $b->water);
        $expenses = (float) Expense::query()
            ->when($propertyId, fn ($q) => $q->where('property_id', $propertyId))
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', (int) substr($month, 5, 2))
            ->sum('amount');

        $units = Unit::query()
            ->where('is_deleted', false)
            ->when($propertyId, fn ($q) => $q->where('property_id', $propertyId));

        $totalUnits = (clone $units)->count();
        $occupiedUnits = (clone $units)->where('status', 'occupied')->count();
        $occupancyRate = $totalUnits > 0 ? round($occupiedUnits / $totalUnits * 100, 1) : 0;
        $lostRent = (float) (clone $units)->where('status', 'vacant')->sum('monthly_rent');

        return [
            'expected_rent' => round($expectedRent, 2),
            'collected' => round($collected, 2),
            'due' => round($due, 2),
            'utility_billed' => round($utilityBilled, 2),
            'expenses' => round($expenses, 2),
            'net_income' => round($collected - $expenses, 2),
            'occupancy_rate' => $occupancyRate,
            'lost_rent' => round($lostRent, 2),
            'total_units' => $totalUnits,
            'occupied_units' => $occupiedUnits,
        ];
    }

    /**
     * Collection trend for the last N months.
     */
    public function collectionTrend(int $months = 6, ?string $propertyId = null): array
    {
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $bills = Bill::query()
                ->where('billing_month', $month)
                ->when($propertyId, fn ($q) => $q->where('property_id', $propertyId))
                ->withSum('allocations as allocated_sum', 'amount')
                ->get();

            $data[] = [
                'month' => Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                'billed' => round((float) $bills->sum('total'), 2),
                'collected' => round((float) $bills->sum(fn ($b) => $b->totalPaid()), 2),
            ];
        }

        return $data;
    }

    /**
     * Income vs expense by month.
     */
    public function incomeVsExpense(int $months = 6, ?string $propertyId = null): array
    {
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $bills = Bill::query()
                ->where('billing_month', $month->format('Y-m'))
                ->when($propertyId, fn ($q) => $q->where('property_id', $propertyId))
                ->withSum('allocations as allocated_sum', 'amount')
                ->get();

            $expenses = Expense::query()
                ->when($propertyId, fn ($q) => $q->where('property_id', $propertyId))
                ->whereYear('expense_date', $month->year)
                ->whereMonth('expense_date', $month->month)
                ->sum('amount');

            $data[] = [
                'month' => $month->format('M Y'),
                'income' => round((float) $bills->sum(fn ($b) => $b->totalPaid()), 2),
                'expense' => round((float) $expenses, 2),
            ];
        }

        return $data;
    }

    /**
     * Utility collection by type.
     */
    public function utilityCollections(string $billingMonth, ?string $propertyId = null): array
    {
        $bills = Bill::query()
            ->where('billing_month', $billingMonth)
            ->when($propertyId, fn ($q) => $q->where('property_id', $propertyId))
            ->get();

        return [
            'electricity' => round((float) $bills->sum('electricity'), 2),
            'gas' => round((float) $bills->sum('gas'), 2),
            'water' => round((float) $bills->sum('water'), 2),
        ];
    }

    /**
     * Property-level income/expense report for a period.
     */
    public function propertyReport(?string $propertyId = null, ?string $from = null, ?string $to = null): \Illuminate\Support\Collection
    {
        $from = $from ?? now()->startOfYear()->toDateString();
        $to = $to ?? now()->endOfMonth()->toDateString();

        $properties = Property::query()
            ->when($propertyId, fn ($q) => $q->where('id', $propertyId))
            ->with(['bills' => fn ($q) => $q->whereBetween('billing_month', [substr($from, 0, 7), substr($to, 0, 7)]), 'expenses' => fn ($q) => $q->whereBetween('expense_date', [$from, $to])])
            ->get();

        return $properties->map(function (Property $property) {
            $income = (float) $property->bills->sum(fn ($b) => $b->totalPaid());
            $expense = (float) $property->expenses->sum('amount');

            return [
                'property' => $property,
                'billed' => round((float) $property->bills->sum('total'), 2),
                'collected' => round($income, 2),
                'expenses' => round($expense, 2),
                'net' => round($income - $expense, 2),
                'occupancy' => $property->vacancyRate(),
            ];
        });
    }

    /**
     * Payment method breakdown for a period.
     */
    public function paymentMethods(?string $from = null, ?string $to = null): array
    {
        $from = $from ?? now()->startOfYear()->toDateString();
        $to = $to ?? now()->toDateString();

        return Payment::query()
            ->whereBetween('payment_date', [$from, $to])
            ->selectRaw('method, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('method')
            ->pluck('total', 'method')
            ->map(fn ($v) => round((float) $v, 2))
            ->toArray();
    }
}
