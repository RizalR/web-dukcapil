<?php

namespace Database\Seeders;

use App\AdministrativeAreaLevel;
use App\LinkCategory;
use App\Models\Employee;
use App\Models\ExternalLink;
use App\Models\NewsArticle;
use App\Models\PopulationStatistic;
use App\Models\PublicDocument;
use App\Models\SupplyStatus;
use App\Models\User;
use App\NewsStatus;
use App\SupplyLevel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::query()->where('role', 'super_admin')->first();

        $news = [
            'title' => 'Informasi Kependudukan Kabupaten Gorontalo Semester II 2025',
            'slug' => 'informasi-kependudukan-kabupaten-gorontalo-semester-ii-2025',
            'excerpt' => 'Ringkasan data penduduk Kabupaten Gorontalo dan ketersediaan bahan pencetakan dokumen per 12 Februari 2026.',
            'body' => "Dinas Kependudukan dan Pencatatan Sipil Kabupaten Gorontalo menyajikan pembaruan agregat penduduk Semester II Tahun 2025. Data ini ditampilkan sebagai informasi publik dan bukan layanan pengajuan dokumen.\n\nUntuk pengurusan dokumen kependudukan, masyarakat dapat menggunakan tautan layanan SI PRIMA yang tersedia pada situs ini.",
            'status' => NewsStatus::Published,
            'is_featured' => true,
            'source_url' => 'https://dukcapil.gorontalokab.go.id/',
            'published_at' => '2026-02-12 08:00:00',
        ];

        if ($superAdmin) {
            $news['author_id'] = $superAdmin->id;
            NewsArticle::query()->updateOrCreate(['slug' => $news['slug']], $news);
        }

        foreach ([
            ['label' => PopulationStatistic::LABEL_TOTAL, 'value' => 428370],
            ['label' => PopulationStatistic::LABEL_MALE, 'value' => 215708],
            ['label' => PopulationStatistic::LABEL_FEMALE, 'value' => 212662],
        ] as $statistic) {
            PopulationStatistic::query()->updateOrCreate(
                ['label' => $statistic['label'], 'period' => 'Semester II 2025', 'area_code' => '75.01'],
                [...$statistic, 'category' => PopulationStatistic::CATEGORY_GENDER, 'unit' => 'jiwa', 'area' => 'Kabupaten Gorontalo', 'area_level' => AdministrativeAreaLevel::Regency, 'parent_area_code' => null, 'source' => 'Dinas Dukcapil Kabupaten Gorontalo', 'source_url' => 'https://dukcapil.gorontalokab.go.id/', 'published_at' => '2026-02-12 08:00:00', 'updated_by' => $superAdmin?->id],
            );
        }

        foreach ([
            ['item' => 'Blangko KTP-el', 'status' => SupplyLevel::Available, 'note' => 'Tersedia'],
            ['item' => 'Ribbon KTP-el', 'status' => SupplyLevel::Available, 'note' => 'Tersedia'],
            ['item' => 'Blangko KIA', 'status' => SupplyLevel::Available, 'note' => 'Tersedia'],
            ['item' => 'Ribbon KIA', 'status' => SupplyLevel::Unavailable, 'note' => 'Belum tersedia'],
        ] as $supply) {
            SupplyStatus::query()->updateOrCreate(
                ['location' => 'Kantor Dukcapil Limboto', 'item' => $supply['item']],
                [...$supply, 'source' => 'Dinas Dukcapil Kabupaten Gorontalo', 'checked_at' => '2026-02-12 08:00:00', 'updated_by' => $superAdmin?->id],
            );
        }

        foreach ([
            ['title' => 'Standar Pelayanan', 'category' => 'Pelayanan', 'description' => 'Standar pelayanan administrasi kependudukan.', 'url' => 'https://dukcapil.gorontalokab.go.id/profil/'],
            ['title' => 'Visi dan Misi Kabupaten Gorontalo', 'category' => 'Profil Pemerintah', 'description' => 'Visi dan misi kepala daerah Kabupaten Gorontalo.', 'url' => 'https://gorontalokab.go.id/halaman.php?slug=visi-misi-kepala-daerah'],
            ['title' => 'Dokumen Perencanaan Daerah', 'category' => 'Perencanaan', 'description' => 'Portal dokumen perencanaan Bapelitbangda Kabupaten Gorontalo.', 'url' => 'https://bapelitbangda.gorontalokab.go.id/dokumen'],
            ['title' => 'Regulasi Administrasi Kependudukan', 'category' => 'Regulasi', 'description' => 'Jaringan Dokumentasi dan Informasi Hukum Kementerian Dalam Negeri.', 'url' => 'https://jdih.kemendagri.go.id/'],
        ] as $document) {
            PublicDocument::query()->updateOrCreate(
                ['slug' => str($document['title'])->slug()],
                [...$document, 'year' => 2026, 'published_at' => now(), 'updated_by' => $superAdmin?->id],
            );
        }

        Employee::query()->updateOrCreate(
            ['name' => 'Muhtar Taufik Saleh Nuna'],
            ['position' => 'Kepala Dinas', 'unit' => 'Dinas Kependudukan dan Pencatatan Sipil Kabupaten Gorontalo', 'sort_order' => 1, 'is_published' => true, 'updated_by' => $superAdmin?->id],
        );

        foreach ([
            ['name' => 'SI PRIMA', 'category' => LinkCategory::Service, 'description' => 'Layanan administrasi kependudukan daring Kabupaten Gorontalo.', 'url' => config('dukcapil.service_url')],
            ['name' => 'e-Kinerja', 'category' => LinkCategory::EmployeeApp, 'description' => 'Aplikasi kinerja pegawai Kabupaten Gorontalo.', 'url' => 'https://ekinerja.gorontalokab.go.id/'],
            ['name' => 'BRO BKPSDM', 'category' => LinkCategory::EmployeeApp, 'description' => 'Portal Badan Kepegawaian dan Pengembangan SDM Kabupaten Gorontalo.', 'url' => 'https://bkpsdm.gorontalokab.go.id/'],
            ['name' => 'SIMDA-NG', 'category' => LinkCategory::EmployeeApp, 'description' => 'Aplikasi pengelolaan keuangan pemerintah daerah.', 'url' => 'https://simda.bpkp.go.id/'],
            ['name' => 'e-Monep', 'category' => LinkCategory::EmployeeApp, 'description' => 'Aplikasi monitoring dan evaluasi pembangunan.', 'url' => 'https://gorontalokab.e-monep.com/login'],
            ['name' => 'SIPD RI', 'category' => LinkCategory::EmployeeApp, 'description' => 'Sistem Informasi Pemerintahan Daerah Republik Indonesia.', 'url' => 'https://sipd-ri.kemendagri.go.id/'],
        ] as $index => $link) {
            ExternalLink::query()->updateOrCreate(
                ['name' => $link['name']],
                [...$link, 'is_active' => true, 'sort_order' => $index + 1],
            );
        }
    }
}
