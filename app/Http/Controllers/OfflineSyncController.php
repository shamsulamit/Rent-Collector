<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Meter;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Tenant;
use App\Services\Billing\BillingService;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * JSON endpoints consumed by the PWA offline queue when the device is back online.
 */
class OfflineSyncController extends Controller
{
    public function meta(): JsonResponse
    {
        return response()->json([
            'user' => auth()->user()?->only('id', 'name', 'email'),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function properties(): JsonResponse
    {
        return response()->json(Property::active()->with(['floors', 'units'])->get());
    }

    public function tenants(): JsonResponse
    {
        return response()->json(Tenant::where('is_deleted', false)->get());
    }

    public function bills(Request $request): JsonResponse
    {
        $bills = Bill::query()
            ->with(['tenant', 'unit', 'property'])
            ->when($request->month, fn ($q) => $q->where('billing_month', $request->month))
            ->latest('billing_month')
            ->limit(200)
            ->get();

        return response()->json($bills->map(fn (Bill $b) => array_merge($b->toArray(), [
            'paid' => $b->totalPaid(),
            'balance' => $b->balance(),
        ])));
    }

    public function storeReading(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'meter_id' => 'required|exists:meters,id',
            'billing_month' => 'required|date_format:Y-m',
            'current_reading' => 'required|numeric|min:0',
            'reading_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $meter = Meter::with('unit.activeTenancy')->findOrFail($validated['meter_id']);
        $previous = $meter->lastReading()?->current_reading ?? $meter->starting_reading;

        try {
            $reading = DB::transaction(function () use ($meter, $validated, $previous) {
                $reading = MeterReading::updateOrCreate(
                    ['meter_id' => $meter->id, 'billing_month' => $validated['billing_month']],
                    [
                        'property_id' => $meter->property_id,
                        'unit_id' => $meter->unit_id,
                        'tenant_id' => $meter->unit->activeTenancy?->tenant_id,
                        'tenancy_id' => $meter->unit->activeTenancy?->id,
                        'previous_reading' => $previous,
                        'current_reading' => $validated['current_reading'],
                        'usage' => max(0, (float) $validated['current_reading'] - (float) $previous),
                        'reading_date' => $validated['reading_date'] ?? now()->toDateString(),
                        'status' => 'submitted',
                        'entered_by' => auth()->id(),
                        'notes' => $validated['notes'] ?? null,
                    ]
                );

                \App\Models\AuditLog::log('meter_reading.sync', 'MeterReading', $reading->id);

                return $reading;
            });

            return response()->json(['ok' => true, 'reading' => $reading]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function storePayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'method' => 'required|in:cash,bank,bkash,nagad,rocket',
            'reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $tenant = Tenant::findOrFail($validated['tenant_id']);

        try {
            $payment = app(PaymentService::class)->record($validated + [
                'property_id' => $tenant->activeTenancy?->property_id,
                'unit_id' => $tenant->activeTenancy?->unit_id,
                'tenancy_id' => $tenant->activeTenancy?->id,
                'recorded_by' => auth()->id(),
            ], 'oldest-first');

            return response()->json(['ok' => true, 'payment' => $payment]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
