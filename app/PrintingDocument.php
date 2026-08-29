<?php

namespace App;

enum PrintingDocument: string
{
    case ElectronicIdentityCard = 'ktp_el';
    case ChildIdentityCard = 'kia';

    public function label(): string
    {
        return match ($this) {
            self::ElectronicIdentityCard => 'Pencetakan KTP-el',
            self::ChildIdentityCard => 'Pencetakan KIA',
        };
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::ElectronicIdentityCard => 10,
            self::ChildIdentityCard => 20,
        };
    }
}
