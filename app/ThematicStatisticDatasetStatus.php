<?php

namespace App;

enum ThematicStatisticDatasetStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Superseded = 'superseded';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Published => 'Terbit',
            self::Superseded => 'Digantikan',
        };
    }
}
