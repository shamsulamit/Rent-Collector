@extends('layouts.app')
@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Import / Export</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Bulk-import records from CSV or export current data.</p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success mt-4">{{ session('message') }}</div>
    @endif

    @if ($errors->has('file'))
        <div class="alert alert-error mt-4">{{ $errors->first('file') }}</div>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="card p-5">
            <h3 class="font-bold">Import CSV</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">CSV headers must match the column names shown for each type.</p>
            <form action="{{ route('import.run') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="label">Record type</label>
                    <select name="type" class="input" id="import-type">
                        <option value="tenants">Tenants</option>
                        <option value="units">Units</option>
                        <option value="bills">Bills</option>
                        <option value="payments">Payments</option>
                    </select>
                </div>
                <div>
                    <label class="label">CSV file</label>
                    <input type="file" name="file" accept=".csv,.txt" class="input" required>
                </div>
                <button type="submit" class="btn-primary">Import</button>
            </form>
            <div class="mt-4 rounded-xl bg-slate-50 p-4 text-xs text-slate-500 dark:bg-ink-800 dark:text-slate-400">
                <p class="font-semibold text-slate-600 dark:text-slate-300">Expected headers:</p>
                <p class="mt-1 font-mono" id="import-headers">full_name, phone, whatsapp_number, email, nid</p>
            </div>
        </div>

        <div class="card p-5">
            <h3 class="font-bold">Export CSV</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Download current data in CSV format.</p>
            <div class="mt-4 space-y-2">
                @foreach (['tenants', 'units', 'bills', 'payments', 'expenses', 'readings'] as $type)
                    <a href="{{ route('export', $type) }}" class="btn-ghost w-full justify-between border border-slate-200 px-3 py-2 text-sm dark:border-ink-700">
                        <span class="capitalize">{{ $type }}</span>
                        <span class="text-slate-400">.csv</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const typeHeaders = {
                tenants: 'full_name, phone, whatsapp_number, email, nid',
                units: 'property_id, name, monthly_rent, status',
                bills: 'tenant_id, billing_month, rent, total',
                payments: 'tenant_id, amount, payment_date, method',
            };
            const importType = document.getElementById('import-type');
            if (importType) {
                importType.addEventListener('change', (e) => {
                    document.getElementById('import-headers').textContent = typeHeaders[e.target.value] || '';
                });
            }
        </script>
    @endpush
@endsection
