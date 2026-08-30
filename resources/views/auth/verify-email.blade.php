@extends('layouts.guest')

@section('content')
    <h2 class="text-xl font-bold text-slate-900">Verify your email</h2>
    <p class="mt-2 text-sm text-slate-500">
        Thanks for signing up! Before getting started, verify your email address by clicking the link we emailed you.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="mt-4 rounded-xl border border-brand/30 bg-brand/10 px-4 py-3 text-sm text-brand-dark">
            A fresh verification link has been sent to your email.
        </div>
    @endif

    <div class="mt-6 space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn-primary w-full">Resend verification email</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-secondary w-full">Log out</button>
        </form>
    </div>
@endsection
