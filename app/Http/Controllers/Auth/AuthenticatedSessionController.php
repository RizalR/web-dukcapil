<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $key = Str::transliterate(Str::lower($request->string('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => __('Terlalu banyak percobaan. Coba lagi dalam :seconds detik.', ['seconds' => RateLimiter::availableIn($key)])]);
        }

        $request->session()->forget([
            TwoFactorChallengeController::SESSION_USER_ID,
            TwoFactorChallengeController::SESSION_REMEMBER,
        ]);

        $credentials = [
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
            'is_active' => true,
        ];
        $provider = Auth::guard('web')->getProvider();
        $user = $provider->retrieveByCredentials($credentials);

        if (! $user instanceof User || ! $provider->validateCredentials($user, $credentials)) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'Email atau kata sandi tidak sesuai.']);
        }

        RateLimiter::clear($key);
        $provider->rehashPasswordIfRequired($user, $credentials);
        $request->session()->regenerate();

        if ($user->isSuperAdmin() && $user->hasConfirmedTwoFactorAuthentication()) {
            $request->session()->put([
                TwoFactorChallengeController::SESSION_USER_ID => $user->getKey(),
                TwoFactorChallengeController::SESSION_REMEMBER => $request->boolean('remember'),
            ]);

            return redirect()->route('two-factor.challenge');
        }

        Auth::guard('web')->login($user, $request->boolean('remember'));
        $auditLogger->record('auth.login', 'Pengguna masuk ke panel administrasi.');

        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.two-factor.edit');
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $auditLogger->record('auth.logout', 'Pengguna keluar dari panel administrasi.');
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
