<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Expense;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\Unit;
use App\Services\AuditService;
use App\Services\ImportExport\ImportExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class ImportExportController extends Controller
{
    public function __construct(protected ImportExportService $service, protected AuditService $audit) {}

    public function importForm(): View
    {
        return view('import.index');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'type' => 'required|in:tenants,units,bills,payments',
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file')->getRealPath();
        $columnMap = $this->columnMap($request->type);

        try {
            $rows = $this->service->parse($file, $columnMap);
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Could not parse CSV: '.$e->getMessage()]);
        }

        $this->audit->record('import.run', null, null, ['type' => $request->type, 'rows' => count($rows)]);
        session()->flash('message', 'Imported '.count($rows).' rows of '.$request->type.'.');

        return back();
    }

    protected function columnMap(string $type): array
    {
        return match ($type) {
            'tenants' => ['full_name' => 'full_name', 'phone' => 'phone', 'whatsapp_number' => 'whatsapp_number', 'email' => 'email', 'nid' => 'nid'],
            'units' => ['property_id' => 'property_id', 'name' => 'name', 'monthly_rent' => 'monthly_rent', 'status' => 'status'],
            'bills' => ['tenant_id' => 'tenant_id', 'billing_month' => 'billing_month', 'rent' => 'rent', 'total' => 'total'],
            'payments' => ['tenant_id' => 'tenant_id', 'amount' => 'amount', 'payment_date' => 'payment_date', 'method' => 'method'],
            default => [],
        };
    }

    public function export(Request $request, string $type): \Symfony\Component\HttpFoundation\Response
    {
        $rows = match ($type) {
            'tenants' => Tenant::where('is_deleted', false)->get(['full_name', 'phone', 'whatsapp_number', 'email', 'nid']),
            'units' => Unit::with('property')->get()->map(fn ($u) => [
                'property' => $u->property?->name, 'name' => $u->name,
                'monthly_rent' => $u->monthly_rent, 'status' => $u->status,
            ]),
            'bills' => Bill::with('tenant')->get()->map(fn ($b) => [
                'bill_no' => $b->bill_no, 'tenant' => $b->tenant?->full_name,
                'billing_month' => $b->billing_month, 'total' => $b->total, 'status' => $b->status,
            ]),
            'payments' => Payment::with('tenant')->get()->map(fn ($p) => [
                'reference' => $p->reference, 'tenant' => $p->tenant?->full_name,
                'amount' => $p->amount, 'payment_date' => $p->payment_date, 'method' => $p->method,
            ]),
            'expenses' => Expense::with('property')->get()->map(fn ($e) => [
                'property' => $e->property?->name, 'category' => $e->category,
                'amount' => $e->amount, 'expense_date' => $e->expense_date,
            ]),
            'readings' => MeterReading::with('meter')->get()->map(fn ($r) => [
                'meter_number' => $r->meter?->meter_number, 'billing_month' => $r->billing_month,
                'previous_reading' => $r->previous_reading, 'current_reading' => $r->current_reading,
                'usage' => $r->usage, 'status' => $r->status,
            ]),
            default => collect(),
        };

        $headers = $rows->first() ? array_keys($rows->first()->toArray()) : [];
        $csv = $this->service->writer($headers, collect($rows));

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$type.'-'.now()->format('Ymd').'.csv"',
        ]);
    }
}
