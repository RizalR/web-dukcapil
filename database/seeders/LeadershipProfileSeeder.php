<?php

namespace Database\Seeders;

use App\LeadershipRole;
use App\Models\LeadershipProfile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeadershipProfileSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::query()->where('role', 'super_admin')->first();

        foreach ([
            [
                'role' => LeadershipRole::Regent,
                'name' => 'H. Sofyan Puhi, S.T.',
                'photo_path' => 'images/leaders/sofyan-puhi.jpg',
            ],
            [
                'role' => LeadershipRole::ViceRegent,
                'name' => 'H. Tonny S. Junus',
                'photo_path' => 'images/leaders/tonny-s-junus.jpg',
            ],
            [
                'role' => LeadershipRole::DepartmentHead,
                'name' => 'Muhtar Taufik Saleh Nuna, S.STP., M.E.',
                'photo_path' => 'images/leaders/muhtar-taufik-saleh-nuna.jpg',
            ],
        ] as $profile) {
            LeadershipProfile::query()->updateOrCreate(
                ['role' => $profile['role']],
                [
                    ...$profile,
                    'sort_order' => $profile['role']->sortOrder(),
                    'is_published' => true,
                    'updated_by' => $superAdmin?->id,
                ],
            );
        }
    }
}
