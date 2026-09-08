<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Electricity Bill {{ $bill->billing_month }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #0f172a; font-size: 12px; padding: 32px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #0d9488; padding-bottom: 16px; }
        .brand { font-size: 20px; font-weight: bold; color: #0d9488; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th { text-align: left; background: #f1f5f9; padding: 8px; font-size: 10px; text-transform: uppercase; color: #475569; }
        td { padding: 10px 8px; border-bottom: 1px solid #e2e8f0; }
        td.amt { text-align: right; }
        .total-row td { font-weight: bold; border-top: 2px solid #0d9488; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="brand">{{ $company }}</div>
            <div style="font-size:10px;color:#64748b;">Postpaid Electricity Bill</div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:16px;font-weight:bold;">ELECTRICITY INVOICE</div>
            <div>Month: {{ \Carbon\Carbon::createFromFormat('Y-m', $bill->billing_month)->format('F Y') }}</div>
            <div>Status: {{ strtoupper($bill->status) }}</div>
        </div>
    </div>

    <p style="margin-top:20px;"><strong>{{ $bill->tenant?->full_name ?? '—' }}</strong><br>
        {{ $bill->property?->name }} · {{ $bill->unit?->name }}<br>
        Meter {{ $bill->meter?->meter_number }} · {{ $bill->tariff?->name }}</p>

    <table>
        <tr><th>Description</th><th class="amt">Amount (৳)</th></tr>
        @foreach ($bill->lineItems() as $item)
            <tr><td>{{ $item['label'] }}</td><td class="amt">{{ number_format($item['amount'], 2) }}</td></tr>
        @endforeach
        <tr class="total-row"><td>Total</td><td class="amt">৳{{ number_format($bill->total, 2) }}</td></tr>
    </table>
</body>
</html>
