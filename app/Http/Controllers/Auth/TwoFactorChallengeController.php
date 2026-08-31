<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactorChallengeRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\TwoFactorAuthenticationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class TwoFactorChallengeController extends Controller
{
    public const SESSION_USER_ID = 'auth.two_factor.user_id';

    public const SESSION_REMEMBER = 'auth.two_factor.remember';

    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has(self::SESSION_USER_ID)) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Sesi verifikasi telah berakhir. Silakan masuk kembali.']);
        }

        return view('auth.two-factor-challenge');
    }

    public function store(
        TwoFactorChallengeRequest $request,
        TwoFactorAuthenticationService $twoFactorAuthentication,
        AuditLogger $auditLogger,
    ): RedirectResponse {
        $user = User::query()->find($request->session()->get(self::SESSION_USER_ID));

        if (! $user?->is_active || ! $user->isSuperAdmin() || ! $user->hasConfirmedTwoFactorAuthentication()) {
            $this->clearPendingAuthentication($request);

            return redirect()->route('login')
                ->withErrors(['email' => 'Sesi verifikasi tidak valid. Silakan masuk kembali.']);
        }

        $key = 'two-factor|'.$user->getKey().'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'code' => __('Terlalu banyak percobaan. Coba lagi dalam :seconds detik.', [
                    'seconds' => RateLimiter::availableIn($key),
                ]),
            ]);
        }

        $usesRecoveryCode = $request->filled('recovery_code');
        $isValid = $usesRecoveryCode
            ? $twoFactorAuthentication->consumeRecoveryCode($user, $request->string('recovery_code')->toString())
            : $twoFactorAuthentication->verifyCode($user, $request->string('code')->toString());

        if (! $isValid) {
            RateLimiter::hit($key, 60);
            $auditLogger->recordForUser($user, 'auth.mfa.failed', 'Verifikasi dua faktor gagal.');

            throw ValidationException::withMessages([
                'code' => 'Kode authenticator atau recovery code tidak sesuai.',
            ]);
        }

        RateLimiter::clear($key);
        $remember = $request->session()->get(self::SESSION_REMEMBER, false) === true;
        $this->clearPendingAuthentication($request);
        Auth::guard('web')->login($user, $remember);
        $request->session()->regenerate();
        $auditLogger->record('auth.login', 'Super Admin masuk dengan autentikasi dua faktor.', properties: [
            'mfa_method' => $usesRecoveryCode ? 'recovery_code' : 'totp',
        ]);

        return redirect()->intended(route('admin.dashboard'));
    }

    private function clearPendingAuthentication(Request $request): void
    {
        $request->session()->forget([
            self::SESSION_USER_ID,
            self::SESSION_REMEMBER,
        ]);
    }
}
