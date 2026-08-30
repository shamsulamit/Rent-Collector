<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bill {{ $bill->bill_no }}</title>
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
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th { text-align: left; background: #f1f5f9; padding: 8px; font-size: 10px; text-transform: uppercase; color: #475569; }
        td { padding: 10px 8px; border-bottom: 1px solid #e2e8f0; }
        td.amt { text-align: right; }
        .total-row td { font-weight: bold; border-top: 2px solid #0d9488; border-bottom: none; }
        .grand { background: #f0fdfa; }
        .footer { margin-top: 40px; padding-top: 12px; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 10px; }
        .status { display: inline-block; padding: 4px 12px; border-radius: 999px; background: #fef3c7; color: #92400e; font-size: 10px; font-weight: bold; }
        .immutable { margin-top: 8px; font-size: 10px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="brand">{{ $company }}</div>
            <div style="font-size:10px;color:#64748b;">Property &amp; Landlord Management</div>
        </div>
        <div class="meta">
            <span style="font-size:16px;font-weight:bold;">RENTAL INVOICE</span>
            <span>Bill No: {{ $bill->bill_no }}</span>
            <span>Month: {{ \Carbon\Carbon::createFromFormat('Y-m', $bill->billing_month)->format('F Y') }}</span>
        </div>
    </div>

    <div class="grid">
        <div>
            <div class="label">Billed To</div>
            <div class="value">{{ $bill->tenant?->full_name ?? '—' }}</div>
            <div style="color:#64748b;">{{ $bill->tenant?->phone }}</div>
            <div style="color:#64748b;">{{ $bill->tenant?->address }}</div>
        </div>
        <div style="text-align:right;">
            <div class="label">Property</div>
            <div class="value">{{ $bill->property?->name ?? '—' }}</div>
            <div style="color:#64748b;">{{ $bill->unit?->name }}</div>
            @if ($bill->unit?->floor)
                <div style="color:#64748b;">Floor {{ $bill->unit->floor->name }}</div>
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="amt">Amount (৳)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bill->lineItems() as $item)
                <tr>
                    <td>{{ $item['label'] }}</td>
                    <td class="amt">{{ number_format($item['amount'], 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row grand">
                <td>Total Payable</td>
                <td class="amt">৳{{ number_format((float) $bill->total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top:24px;display:flex;justify-content:space-between;">
        <div>
            <div class="label">Amount Paid</div>
            <div class="value">৳{{ number_format($bill->totalPaid(), 2) }}</div>
        </div>
        <div style="text-align:right;">
            <div class="label">Balance Due</div>
            <div class="value" style="color:{{ $bill->balance() > 0 ? '#dc2626' : '#0d9488' }};">৳{{ number_format($bill->balance(), 2) }}</div>
        </div>
    </div>

    <div style="margin-top:24px;">
        <span class="status">STATUS: {{ strtoupper(str_replace('_', ' ', $bill->status)) }}</span>
    </div>
    @if ($bill->isImmutable())
        <div class="immutable">
            Finalized on {{ $bill->finalized_at ? $bill->finalized_at->format('d M Y') : '—' }}
            @if ($bill->finalizer) by {{ $bill->finalizer->name }} @endif
        </div>
    @endif

    <div class="footer">
        <div>{{ $company }} &middot; Please pay by the due date. Utilities and service charges are billed per tariff schedule.</div>
        <div>Generated {{ now()->format('d M Y H:i') }} &middot; This is a computer-generated document.</div>
    </div>
</body>
</html>
