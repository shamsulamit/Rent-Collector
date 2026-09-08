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
        <tr><td>Previous reading {{ number_format($bill->previous_reading, 1) }} → Current {{ number_format($bill->current_reading, 1) }} ({{ number_format($bill->usage, 1) }} units)</td><td class="amt">{{ number_format($bill->energy_charge, 2) }}</td></tr>
        @if ($bill->fixed_charge > 0)<tr><td>Fixed charge</td><td class="amt">{{ number_format($bill->fixed_charge, 2) }}</td></tr>@endif
        @if ($bill->service_charge > 0)<tr><td>Service charge</td><td class="amt">{{ number_format($bill->service_charge, 2) }}</td></tr>@endif
        @if ($bill->demand_charge > 0)<tr><td>Demand charge</td><td class="amt">{{ number_format($bill->demand_charge, 2) }}</td></tr>@endif
        @if ($bill->other_charge > 0)<tr><td>Other</td><td class="amt">{{ number_format($bill->other_charge, 2) }}</td></tr>@endif
        @if ($bill->vat > 0)<tr><td>VAT</td><td class="amt">{{ number_format($bill->vat, 2) }}</td></tr>@endif
        @if ($bill->discount > 0)<tr><td>Discount</td><td class="amt">-{{ number_format($bill->discount, 2) }}</td></tr>@endif
        @if ($bill->adjustment != 0)<tr><td>Adjustment</td><td class="amt">{{ number_format($bill->adjustment, 2) }}</td></tr>@endif
        <tr class="total-row"><td>Total</td><td class="amt">৳{{ number_format($bill->total, 2) }}</td></tr>
    </table>
</body>
</html>
