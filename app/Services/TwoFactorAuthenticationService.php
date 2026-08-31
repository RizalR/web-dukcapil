<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Fortify;

class TwoFactorAuthenticationService
{
    public function __construct(private readonly TwoFactorAuthenticationProvider $provider) {}

    public function verifyCode(User $user, string $code): bool
    {
        if ($user->two_factor_secret === null) {
            return false;
        }

        try {
            $secret = Fortify::currentEncrypter()->decrypt($user->two_factor_secret);
        } catch (DecryptException) {
            return false;
        }

        return $this->provider->verify($secret, $code);
    }

    public function consumeRecoveryCode(User $user, string $submittedCode): bool
    {
        if ($user->two_factor_recovery_codes === null) {
            return false;
        }

        try {
            $recoveryCodes = $user->recoveryCodes();
        } catch (DecryptException) {
            return false;
        }

        $normalizedCode = Str::lower(trim($submittedCode));

        foreach ($recoveryCodes as $recoveryCode) {
            if (hash_equals(Str::lower($recoveryCode), $normalizedCode)) {
                $user->replaceRecoveryCode($recoveryCode);

                return true;
            }
        }

        return false;
    }
}
