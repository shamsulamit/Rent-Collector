<?php

namespace App\Services\Payments;

use App\Models\Bill;
use App\Models\Payment;
use App\Models\Tenant;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(protected AuditService $audit) {}

    /**
     * Record a payment and allocate it across bills.
     */
    public function record(array $data, string $strategy = 'oldest-first'): Payment
    {
        return DB::transaction(function () use ($data, $strategy) {
            $payment = Payment::create($data);

            $this->allocate($payment, $strategy);
            $this->audit->record('payment.recorded', 'Payment', $payment->id, $payment->toArray());

            return $payment;
        });
    }

    /**
     * Allocate an unallocated payment across a tenant's due bills.
     */
    public function allocate(Payment $payment, string $strategy = 'oldest-first'): array
    {
        $remaining = $payment->unallocated();
        $allocations = [];

        if ($remaining <= 0) {
            return $allocations;
        }

        $bills = Bill::query()
            ->where('tenant_id', $payment->tenant_id)
            ->whereIn('status', ['finalized', 'calculated', 'due', 'partial', 'paid', 'overpaid'])
            ->withSum('allocations as allocated', 'amount')
            ->get()
            ->filter(fn (Bill $bill) => round((float) $bill->total - (float) ($bill->allocated ?? 0), 2) > 0);

        $bills = match ($strategy) {
            'current-first' => $bills->sortByDesc(fn ($b) => $b->billing_month),
            'manual' => $bills,
            default => $bills->sortBy(fn ($b) => $b->billing_month),
        };

        foreach ($bills as $bill) {
            if ($remaining <= 0) {
                break;
            }

            $billBalance = round((float) $bill->total - (float) ($bill->allocated ?? 0), 2);
            $amount = min($remaining, $billBalance);
            if ($amount <= 0) {
                continue;
            }

            $payment->allocations()->create([
                'bill_id' => $bill->id,
                'amount' => $amount,
                'strategy' => $strategy,
                'allocated_by' => auth()->id(),
            ]);

            $this->refreshBillStatus($bill);
            $remaining = round($remaining - $amount, 2);
            $allocations[] = ['bill' => $bill->bill_no, 'amount' => $amount];
        }

        $this->audit->record('payment.allocated', 'Payment', $payment->id, ['strategy' => $strategy, 'allocations' => $allocations]);

        return $allocations;
    }

    /**
     * Recalculate a bill's status based on payments allocated to it.
     */
    public function refreshBillStatus(Bill $bill): void
    {
        $balance = $bill->balance();

        $hasAllocations = $bill->allocations()->count() > 0;

        $status = match (true) {
            $balance < 0 => 'overpaid',
            $balance == 0 && $hasAllocations => 'paid',
            $hasAllocations => 'partial',
            $bill->finalized_at => 'finalized',
            default => $bill->status === 'draft' ? 'draft' : 'due',
        };

        $bill->update(['status' => $status]);
    }

    /**
     * Tenant carry-forward credit (overpayment balance).
     */
    public function tenantCredit(Tenant $tenant): float
    {
        return Payment::query()
            ->where('tenant_id', $tenant->id)
            ->get()
            ->sum(fn (Payment $p) => $p->unallocated());
    }

    public function delete(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $bills = $payment->allocations()->with('bill')->get()->pluck('bill')->filter();
            $this->audit->record('payment.deleted', 'Payment', $payment->id, null, $payment->toArray());
            $payment->allocations()->delete();
            $payment->delete();

            foreach ($bills as $bill) {
                $this->refreshBillStatus($bill->fresh());
            }
        });
    }

    public function tenantOutstanding(Tenant $tenant): float
    {
        $billed = Bill::where('tenant_id', $tenant->id)
            ->whereIn('status', ['finalized', 'calculated', 'due', 'partial', 'paid', 'overpaid'])
            ->get()
            ->sum(fn (Bill $b) => $b->balance());

        return round(max(0, $billed - $this->tenantCredit($tenant)), 2);
    }
}
