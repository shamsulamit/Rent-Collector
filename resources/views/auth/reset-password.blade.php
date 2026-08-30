@extends('layouts.guest')

@section('content')
    <h2 class="text-xl font-bold text-slate-900">Set new password</h2>
    <p class="mt-1 text-sm text-slate-500">Choose a new password for your account.</p>

    <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        @if ($errors->any())
            <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-600">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div>
            <label class="label" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus class="input">
        </div>

        <div>
            <label class="label" for="password">New password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" class="input">
        </div>

        <div>
            <label class="label" for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="input">
        </div>

        <button type="submit" class="btn-primary w-full">Reset password</button>
    </form>
@endsection
