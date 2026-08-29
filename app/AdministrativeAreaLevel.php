<?php

namespace App;

enum AdministrativeAreaLevel: string
{
    case Regency = 'kabupaten';
    case District = 'kecamatan';
    case Village = 'desa_kelurahan';
    case Legacy = 'legacy';

    public function label(): string
    {
        return match ($this) {
            self::Regency => 'Kabupaten',
            self::District => 'Kecamatan',
            self::Village => 'Desa/Kelurahan',
            self::Legacy => 'Data umum',
        };
    }
}
