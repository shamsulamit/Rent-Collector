<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function __construct(protected AuditService $audit) {}

    /**
     * Redirect to Google for sign-in. Requires GOOGLE_CLIENT_ID/SECRET.
     */
    public function redirect(): RedirectResponse
    {
        if (! Config::get('services.google.client_id')) {
            return redirect()->route('login')->withErrors([
                'google' => 'Google Sign-In is not configured. Contact the administrator.',
            ]);
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors(['google' => 'Google Sign-In failed.']);
        }

        $user = \App\Models\User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            $user = \App\Models\User::create([
                'name' => $googleUser->getName() ?? $googleUser->getEmail(),
                'email' => $googleUser->getEmail(),
                'password' => \Illuminate\Support\Str::random(32),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('staff');
        }

        Auth::login($user);
        $this->audit->record('auth.google_login', 'User', $user->id);

        return redirect()->intended(route('dashboard'));
    }
}
