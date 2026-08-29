<?php

namespace Database\Seeders;

use App\Models\PrintingStatus;
use App\Models\User;
use App\PrintingDocument;
use Illuminate\Database\Seeder;

class PrintingStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::query()->where('role', 'super_admin')->first();

        foreach ([
            [PrintingDocument::ElectronicIdentityCard, true, 'Pencetakan KTP-el tersedia.'],
            [PrintingDocument::ChildIdentityCard, false, 'Pencetakan KIA belum tersedia.'],
        ] as [$document, $isAvailable, $note]) {
            PrintingStatus::query()->updateOrCreate(
                ['location' => 'Kantor Dukcapil Limboto', 'document' => $document],
                [
                    'sort_order' => $document->sortOrder(),
                    'is_available' => $isAvailable,
                    'note' => $note,
                    'source' => 'Dinas Dukcapil Kabupaten Gorontalo',
                    'checked_at' => '2026-02-12 08:00:00',
                    'updated_by' => $superAdmin?->id,
                ],
            );
        }
    }
}
