<?php

namespace App\Support;

use Illuminate\Support\Str;

final class AdministrativeAreaCodeNormalizer
{
    public function normalize(string $areaCode): string
    {
        $areaCode = trim($areaCode);

        if (preg_match('/^\d{2}\.\d{2}(?:\.\d{2}(?:\.\d{4})?)?$/', $areaCode) === 1) {
            return $areaCode;
        }

        if (preg_match('/^\d+$/', $areaCode) !== 1) {
            return $areaCode;
        }

        return match (Str::length($areaCode)) {
            4 => Str::substr($areaCode, 0, 2).'.'.Str::substr($areaCode, 2, 2),
            6 => Str::substr($areaCode, 0, 2).'.'.Str::substr($areaCode, 2, 2).'.'.Str::substr($areaCode, 4, 2),
            10 => Str::substr($areaCode, 0, 2).'.'.Str::substr($areaCode, 2, 2).'.'.Str::substr($areaCode, 4, 2).'.'.Str::substr($areaCode, 6, 4),
            default => $areaCode,
        };
    }
}
