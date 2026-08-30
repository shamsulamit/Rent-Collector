@extends('layouts.guest')

@section('content')
    <h2 class="text-xl font-bold text-slate-900">Create account</h2>
    <p class="mt-1 text-sm text-slate-500">Register to get started.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf

        @if ($errors->any())
            <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-600">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div>
            <label class="label" for="name">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="input">
        </div>

        <div>
            <label class="label" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="input">
        </div>

        <div>
            <label class="label" for="password">Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" class="input">
        </div>

        <div>
            <label class="label" for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="input">
        </div>

        <button type="submit" class="btn-primary w-full">Register</button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Already registered?
        <a href="{{ route('login') }}" class="font-semibold text-brand-dark hover:underline dark:text-brand-light">Sign in</a>
    </p>
@endsection
