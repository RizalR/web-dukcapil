<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConfirmTwoFactorAuthenticationRequest;
use App\Http\Requests\Admin\EnableTwoFactorAuthenticationRequest;
use App\Http\Requests\Admin\RegenerateTwoFactorRecoveryCodesRequest;
use App\Services\AuditLogger;
use App\Services\TwoFactorAuthenticationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;

class TwoFactorAuthenticationController extends Controller
{
    public function edit(Request $request): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        return view('admin.two-factor.edit', [
            'user' => $request->user(),
            'showRecoveryCodes' => $request->session()->pull('show_mfa_recovery_codes', false) === true,
        ]);
    }

    public function store(
        EnableTwoFactorAuthenticationRequest $request,
        EnableTwoFactorAuthentication $enable,
        AuditLogger $auditLogger,
    ): RedirectResponse {
        $request->user()->forceFill(['two_factor_confirmed_at' => null])->save();
        $enable($request->user(), true);
        $auditLogger->record('auth.mfa.enrollment_started', 'Aktivasi autentikasi dua faktor dimulai.');

        return redirect()->route('admin.two-factor.edit')
            ->with('status', 'Pindai QR code, lalu masukkan kode 6 digit untuk menyelesaikan aktivasi.');
    }

    public function update(
        ConfirmTwoFactorAuthenticationRequest $request,
        ConfirmTwoFactorAuthentication $confirm,
        AuditLogger $auditLogger,
    ): RedirectResponse {
        try {
            $confirm($request->user(), $request->string('code')->toString());
        } catch (ValidationException) {
            throw ValidationException::withMessages([
                'code' => 'Kode authenticator tidak sesuai atau sudah pernah digunakan.',
            ]);
        }

        $auditLogger->record('auth.mfa.enabled', 'Autentikasi dua faktor Super Admin diaktifkan.');

        return redirect()->route('admin.two-factor.edit')
            ->with('status', 'Autentikasi dua faktor berhasil diaktifkan.')
            ->with('show_mfa_recovery_codes', true);
    }

    public function regenerateRecoveryCodes(
        RegenerateTwoFactorRecoveryCodesRequest $request,
        TwoFactorAuthenticationService $twoFactorAuthentication,
        GenerateNewRecoveryCodes $generateNewRecoveryCodes,
        AuditLogger $auditLogger,
    ): RedirectResponse {
        if (! $twoFactorAuthentication->verifyCode($request->user(), $request->string('code')->toString())) {
            throw ValidationException::withMessages([
                'code' => 'Kode authenticator tidak sesuai atau sudah pernah digunakan.',
            ]);
        }

        $generateNewRecoveryCodes($request->user());
        $auditLogger->record('auth.mfa.recovery_codes_regenerated', 'Recovery code autentikasi dua faktor diperbarui.');

        return redirect()->route('admin.two-factor.edit')
            ->with('status', 'Recovery code baru berhasil dibuat. Kode lama sudah tidak berlaku.')
            ->with('show_mfa_recovery_codes', true);
    }
}
