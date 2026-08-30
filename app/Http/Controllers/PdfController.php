<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Payment;
use App\Services\Reports\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;

class PdfController extends Controller
{
    public function bill(Bill $bill): Response
    {
        $bill->load(['tenant', 'unit.floor', 'property', 'allocations.payment']);
        $this->authorize('view', $bill);

        $pdf = Pdf::loadView('pdf.bill', [
            'bill' => $bill,
            'company' => \App\Models\Setting::get('company_name', config('landlord.name')),
        ]);

        return $pdf->download('bill-'.$bill->bill_no.'-'.$bill->billing_month.'.pdf');
    }

    public function receipt(Payment $payment): Response
    {
        $payment->load(['tenant', 'unit', 'property', 'allocations.bill']);
        $this->authorize('view', $payment);

        $pdf = Pdf::loadView('pdf.receipt', [
            'payment' => $payment,
            'company' => \App\Models\Setting::get('company_name', config('landlord.name')),
        ]);

        return $pdf->download('receipt-'.$payment->reference.'.pdf');
    }

    public function report(string $report): Response
    {
        $this->authorize('view reports');

        $service = app(ReportService::class);
        $data = match ($report) {
            'income' => $service->incomeVsExpense(12),
            'collection' => $service->collectionTrend(12),
            'property' => $service->propertyReport()->toArray(),
            'utilities' => ['electricity' => $service->utilityCollections(now()->format('Y-m'))],
            default => abort(404),
        };

        $pdf = Pdf::loadView('pdf.report', [
            'report' => $report,
            'data' => $data,
            'company' => \App\Models\Setting::get('company_name', config('landlord.name')),
            'generated_at' => now()->format('Y-m-d H:i'),
        ]);

        return $pdf->download('report-'.$report.'.pdf');
    }
}
