<?php

namespace App;

enum LeadershipRole: string
{
    case Regent = 'regent';
    case ViceRegent = 'vice_regent';
    case DepartmentHead = 'department_head';

    public function label(): string
    {
        return match ($this) {
            self::Regent => 'Bupati Gorontalo',
            self::ViceRegent => 'Wakil Bupati Gorontalo',
            self::DepartmentHead => 'Kepala Dinas Dukcapil Kabupaten Gorontalo',
        };
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::Regent => 1,
            self::ViceRegent => 2,
            self::DepartmentHead => 3,
        };
    }
}
