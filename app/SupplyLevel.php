<?php

namespace App;

enum SupplyLevel: string
{
    case Available = 'available';
    case Limited = 'limited';
    case Unavailable = 'unavailable';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Tersedia',
            self::Limited => 'Terbatas',
            self::Unavailable => 'Tidak tersedia',
        };
    }
}
