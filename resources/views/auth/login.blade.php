@extends('layouts.guest')

@section('content')
    <h2 class="text-xl font-bold text-slate-900">Sign in</h2>
    <p class="mt-1 text-sm text-slate-500">Welcome back. Enter your credentials.</p>

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        @if (session('status'))
            <div class="rounded-xl border border-brand/30 bg-brand/10 px-4 py-3 text-sm text-brand-dark">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-600">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div>
            <label class="label" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="input">
        </div>

        <div>
            <div class="flex items-center justify-between">
                <label class="label" for="password">Password</label>
                <a href="{{ route('password.request') }}" class="text-xs font-medium text-brand-dark hover:underline dark:text-brand-light">Forgot password?</a>
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password" class="input">
        </div>

        <div class="flex items-center gap-2">
            <input id="remember" type="checkbox" name="remember" class="rounded border-slate-300 text-brand focus:ring-brand">
            <label for="remember" class="text-sm text-slate-600">Remember me</label>
        </div>

        <button type="submit" class="btn-primary w-full">Sign in</button>
    </form>

    <div class="mt-6 flex items-center gap-4">
        <div class="h-px flex-1 bg-slate-200"></div>
        <span class="text-xs text-slate-400">or</span>
        <div class="h-px flex-1 bg-slate-200"></div>
    </div>

    <a href="{{ route('google.redirect') }}" class="btn-secondary mt-4 w-full">
        <svg class="h-5 w-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0012 23z"/><path fill="#FBBC05" d="M5.84 14.1a6.6 6.6 0 010-4.2V7.06H2.18a11 11 0 000 9.88l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15A10.96 10.96 0 0012 1 11 11 0 002.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/></svg>
        Continue with Google
    </a>

    @if (! app()->environment('production'))
        <div class="mt-6 rounded-xl bg-slate-50 p-3 text-xs text-slate-500 dark:bg-slate-800 dark:text-slate-400">
            <strong>Demo accounts:</strong> owner@landlord.test / manager@landlord.test &mdash; password: <code>password</code>
        </div>
    @endif

    <p class="mt-6 text-center text-sm text-slate-500">
        Don't have an account?
        <a href="{{ route('register') }}" class="font-semibold text-brand-dark hover:underline dark:text-brand-light">Register</a>
    </p>
@endsection
