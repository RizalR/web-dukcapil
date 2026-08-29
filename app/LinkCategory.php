<?php

namespace App;

enum LinkCategory: string
{
    case Service = 'service';
    case EmployeeApp = 'employee_app';
    case Government = 'government';

    public function label(): string
    {
        return match ($this) {
            self::Service => 'Pelayanan',
            self::EmployeeApp => 'Aplikasi Pegawai',
            self::Government => 'Pemerintahan',
        };
    }
}
