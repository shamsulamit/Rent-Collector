@extends('layouts.app')
@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">WhatsApp History</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Messages sent to tenants via WhatsApp.</p>
        </div>
    </div>

    @if (session()->has('error'))
        <div class="alert alert-error mt-4">{{ session('error') }}</div>
    @endif

    <div class="card mt-4 overflow-x-auto">
        <table class="table-base">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Tenant</th>
                    <th>Phone</th>
                    <th>Template</th>
                    <th>Message</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-ink-700">
                @forelse ($messages as $message)
                    <tr>
                        <td>{{ $message->sent_at ? $message->sent_at->format('d M Y H:i') : $message->created_at->format('d M Y H:i') }}</td>
                        <td class="font-medium">{{ $message->tenant?->full_name ?? '—' }}</td>
                        <td class="font-mono text-xs">{{ $message->phone }}</td>
                        <td><span class="badge bg-slate-100 text-slate-600 dark:bg-ink-700 dark:text-slate-300">{{ $message->template }}</span></td>
                        <td class="max-w-[320px] truncate text-sm text-slate-500 dark:text-slate-400">{{ $message->message }}</td>
                        <td>
                            <span class="badge bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Sent</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-10 text-center text-slate-400">No WhatsApp messages sent yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $messages->links() }}</div>
@endsection
