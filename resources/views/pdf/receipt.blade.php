<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $payment->reference }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #0f172a; font-size: 12px; padding: 32px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #0d9488; padding-bottom: 16px; }
        .brand { font-size: 20px; font-weight: bold; color: #0d9488; }
        .meta { text-align: right; color: #475569; }
        .meta span { display: block; }
        .grid { display: flex; justify-content: space-between; margin-top: 24px; }
        .label { color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: .05em; }
        .value { font-size: 14px; font-weight: bold; margin-top: 2px; }
        .amount-box { margin: 28px auto; width: 70%; border: 2px dashed #0d9488; border-radius: 12px; text-align: center; padding: 24px; }
        .amount-box .big { font-size: 28px; font-weight: bold; color: #0d9488; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th { text-align: left; background: #f1f5f9; padding: 8px; font-size: 10px; text-transform: uppercase; color: #475569; }
        td { padding: 10px 8px; border-bottom: 1px solid #e2e8f0; }
        td.amt { text-align: right; }
        .footer { margin-top: 40px; padding-top: 12px; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="brand">{{ $company }}</div>
            <div style="font-size:10px;color:#64748b;">Property &amp; Landlord Management</div>
        </div>
        <div class="meta">
            <span style="font-size:16px;font-weight:bold;">PAYMENT RECEIPT</span>
            <span>Ref: {{ $payment->reference }}</span>
            <span>Date: {{ $payment->payment_date->format('d M Y') }}</span>
        </div>
    </div>

    <div class="amount-box">
        <div class="label">Amount Received</div>
        <div class="big">৳{{ number_format((float) $payment->amount, 2) }}</div>
        <div style="margin-top:6px;color:#475569;">Method: <strong class="capitalize">{{ $payment->method }}</strong></div>
    </div>

    <div class="grid">
        <div>
            <div class="label">Received From</div>
            <div class="value">{{ $payment->tenant?->full_name ?? '—' }}</div>
            <div style="color:#64748b;">{{ $payment->tenant?->phone }}</div>
        </div>
        <div style="text-align:right;">
            <div class="label">Property</div>
            <div class="value">{{ $payment->property?->name ?? '—' }}</div>
            <div style="color:#64748b;">{{ $payment->unit?->name }}</div>
        </div>
    </div>

    @if ($payment->allocations->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Allocated To</th>
                    <th>Bill</th>
                    <th>Month</th>
                    <th class="amt">Amount (৳)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payment->allocations as $allocation)
                    <tr>
                        <td>{{ $allocation->bill?->tenant?->full_name ?? '—' }}</td>
                        <td>{{ $allocation->bill?->bill_no ?? '—' }}</td>
                        <td>{{ $allocation->bill?->billing_month }}</td>
                        <td class="amt">{{ number_format((float) $allocation->amount, 2) }}</td>
                    </tr>
                @endforeach
                @if ($payment->unallocated() > 0)
                    <tr>
                        <td colspan="3">Carried forward as credit</td>
                        <td class="amt">{{ number_format($payment->unallocated(), 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endif

    @if ($payment->notes)
        <div style="margin-top:16px;color:#475569;">Note: {{ $payment->notes }}</div>
    @endif

    <div class="footer">
        <div>Thank you for your payment. This receipt is generated automatically.</div>
        <div>Recorded {{ $payment->created_at->format('d M Y H:i') }} @if ($payment->recorder) by {{ $payment->recorder->name }} @endif</div>
    </div>
</body>
</html>
