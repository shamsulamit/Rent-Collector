<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report: {{ ucfirst($report) }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #0f172a; font-size: 12px; padding: 32px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #0d9488; padding-bottom: 16px; }
        .brand { font-size: 20px; font-weight: bold; color: #0d9488; }
        .meta { text-align: right; color: #475569; font-size: 11px; }
        h2 { margin-top: 28px; font-size: 16px; }
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
            <span style="font-size:16px;font-weight:bold;">{{ ucfirst($report) }} REPORT</span>
            <span>Generated: {{ $generated_at }}</span>
        </div>
    </div>

    @if ($report === 'income')
        <h2>Income vs Expense (last 12 months)</h2>
        <table>
            <thead><tr><th>Month</th><th class="amt">Income (৳)</th><th class="amt">Expenses (৳)</th><th class="amt">Net (৳)</th></tr></thead>
            <tbody>
                @foreach ($data as $row)
                    <tr>
                        <td>{{ $row['month'] }}</td>
                        <td class="amt">{{ number_format($row['income'], 2) }}</td>
                        <td class="amt">{{ number_format($row['expense'], 2) }}</td>
                        <td class="amt">{{ number_format($row['income'] - $row['expense'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif ($report === 'collection')
        <h2>Collection Trend (last 12 months)</h2>
        <table>
            <thead><tr><th>Month</th><th class="amt">Billed (৳)</th><th class="amt">Collected (৳)</th><th class="amt">Collection %</th></tr></thead>
            <tbody>
                @foreach ($data as $row)
                    <tr>
                        <td>{{ $row['month'] }}</td>
                        <td class="amt">{{ number_format($row['billed'], 2) }}</td>
                        <td class="amt">{{ number_format($row['collected'], 2) }}</td>
                        <td class="amt">{{ $row['billed'] > 0 ? round($row['collected'] / $row['billed'] * 100, 1) : 0 }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif ($report === 'property')
        <h2>Property Summary</h2>
        <table>
            <thead><tr><th>Property</th><th class="amt">Billed (৳)</th><th class="amt">Collected (৳)</th><th class="amt">Expenses (৳)</th><th class="amt">Net (৳)</th><th class="amt">Occupancy</th></tr></thead>
            <tbody>
                @foreach ($data as $row)
                    <tr>
                        <td>{{ $row['property']->name }}</td>
                        <td class="amt">{{ number_format($row['billed'], 2) }}</td>
                        <td class="amt">{{ number_format($row['collected'], 2) }}</td>
                        <td class="amt">{{ number_format($row['expenses'], 2) }}</td>
                        <td class="amt">{{ number_format($row['net'], 2) }}</td>
                        <td class="amt">{{ $row['occupancy'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif ($report === 'utilities')
        <h2>Utility Collections</h2>
        <table>
            <thead><tr><th>Utility</th><th class="amt">Collected (৳)</th></tr></thead>
            <tbody>
                @foreach ($data as $label => $amount)
                    <tr>
                        <td class="capitalize">{{ $label }}</td>
                        <td class="amt">{{ number_format((float) $amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <div>{{ $company }} &middot; Financial report generated on {{ $generated_at }}.</div>
    </div>
</body>
</html>
