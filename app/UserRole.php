<?php

namespace App;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case NewsEditor = 'news_editor';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::NewsEditor => 'Editor Berita',
        };
    }
}
