<?php

namespace Database\Seeders;

use App\Models\User;
use App\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Super Admin Dukcapil',
                'email' => config('dukcapil.admin.super_email'),
                'password' => config('dukcapil.admin.super_password'),
                'role' => UserRole::SuperAdmin,
            ],
            [
                'name' => 'Editor Berita Dukcapil',
                'email' => config('dukcapil.admin.editor_email'),
                'password' => config('dukcapil.admin.editor_password'),
                'role' => UserRole::NewsEditor,
            ],
        ];

        foreach ($accounts as $account) {
            if (! is_string($account['email']) || ! is_string($account['password'])) {
                $this->command?->warn("Akun {$account['name']} dilewati karena kredensial environment belum diisi.");

                continue;
            }

            User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make($account['password']),
                    'role' => $account['role'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
