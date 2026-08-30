<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Landlord Ledger') }}</title>
    <meta name="theme-color" content="#0F172A">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-ink-900 bg-gradient-to-br from-ink-900 via-slate-900 to-emerald-950 px-4 py-12">
    <div class="w-full max-w-md">
        <div class="mb-8 flex flex-col items-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-gradient-to-br from-brand to-emerald-700 text-white shadow-2xl shadow-brand/40">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 21V10m4 11V10m4 11V10m4 11V10m4 11V10M2 10l10-7 10 7"/></svg>
            </div>
            <h1 class="mt-4 text-2xl font-bold text-white">Landlord Ledger</h1>
            <p class="mt-1 text-sm text-slate-400">Property &amp; landlord management</p>
        </div>

        <div class="rounded-2xl border border-white/10 bg-white p-6 shadow-2xl sm:p-8">
            @yield('content')
        </div>

        <p class="mt-6 text-center text-xs text-slate-500">&copy; {{ date('Y') }} Landlord Ledger. All rights reserved.</p>
    </div>
</body>
</html>
