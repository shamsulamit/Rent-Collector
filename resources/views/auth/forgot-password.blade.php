@extends('layouts.guest')

@section('content')
    <h2 class="text-xl font-bold text-slate-900">Reset password</h2>
    <p class="mt-1 text-sm text-slate-500">Forgot your password? Enter your email and we'll send you a reset link.</p>

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
        @csrf

        @if (session('status'))
            <div class="rounded-xl border border-brand/30 bg-brand/10 px-4 py-3 text-sm text-brand-dark">{{ session('status') }}</div>
        @endif

        @error('email')
            <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-600">{{ $message }}</div>
        @enderror

        <div>
            <label class="label" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="input">
        </div>

        <button type="submit" class="btn-primary w-full">Email password reset link</button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        <a href="{{ route('login') }}" class="font-semibold text-brand-dark hover:underline dark:text-brand-light">Back to sign in</a>
    </p>
@endsection
