<?php

namespace Tests\Feature;

use App\AdministrativeAreaLevel;
use App\Models\HeroSlide;
use App\Models\HomepageSetting;
use App\Models\NewsArticle;
use App\Models\PopulationStatistic;
use App\Models\PrintingStatus;
use App\Models\SupplyStatus;
use App\Models\User;
use App\PopulationStatisticCategory;
use App\PrintingDocument;
use App\SupplyLevel;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_displays_published_news_and_security_headers(): void
    {
        $published = NewsArticle::factory()->published()->create(['title' => 'Berita Terbit Dukcapil']);
        NewsArticle::factory()->create(['title' => 'Berita Rahasia Draf']);

        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee('Berita Rahasia Draf')
            ->assertSee(asset('images/logo_kabgor.png'), false)
            ->assertSee(asset('images/dukcapil_.jpeg'), false)
            ->assertSee('Lambang Kabupaten Gorontalo')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Content-Security-Policy');
    }

    public function test_homepage_displays_configured_background_and_only_current_active_slides_in_order(): void
    {
        $this->freezeTime(function (): void {
            $admin = User::factory()->superAdmin()->create();
            HomepageSetting::factory()->create([
                'background_image_path' => 'homepage/backgrounds/custom.jpg',
                'updated_by' => $admin,
            ]);
            HeroSlide::factory()->for($admin, 'updatedBy')->create([
                'label' => 'Urutan Dua',
                'alt_text' => 'Slide urutan dua',
                'sort_order' => 20,
            ]);
            HeroSlide::factory()->for($admin, 'updatedBy')->create([
                'label' => 'Urutan Satu',
                'alt_text' => 'Slide urutan satu',
                'caption' => '<script>alert("slider")</script>',
                'sort_order' => 10,
            ]);
            HeroSlide::factory()->for($admin, 'updatedBy')->create([
                'label' => 'Belum Tayang',
                'alt_text' => 'Slide masa depan',
                'starts_at' => now()->addDay(),
            ]);
            HeroSlide::factory()->for($admin, 'updatedBy')->create([
                'label' => 'Sudah Selesai',
                'alt_text' => 'Slide kedaluwarsa',
                'ends_at' => now()->subDay(),
            ]);
            HeroSlide::factory()->for($admin, 'updatedBy')->create([
                'label' => 'Nonaktif',
                'alt_text' => 'Slide nonaktif',
                'is_active' => false,
            ]);

            $response = $this->get('/');

            $response
                ->assertOk()
                ->assertSee('src="/storage/homepage/backgrounds/custom.jpg"', false)
                ->assertDontSee('http://localhost:8000/storage', false)
                ->assertSeeInOrder(['Slide urutan satu', 'Slide urutan dua'])
                ->assertDontSee('Slide masa depan')
                ->assertDontSee('Slide kedaluwarsa')
                ->assertDontSee('Slide nonaktif')
                ->assertSee('&lt;script&gt;alert(&quot;slider&quot;)&lt;/script&gt;', false)
                ->assertDontSee('<script>alert("slider")</script>', false);
        });
    }

    public function test_homepage_and_services_page_display_current_printing_statuses(): void
    {
        $ktpStatus = PrintingStatus::query()->where('document', PrintingDocument::ElectronicIdentityCard->value)->sole();
        $ktpStatus->update([
            'is_available' => true,
            'note' => 'Pencetakan tersedia selama jam pelayanan.',
            'source' => 'Operator pencetakan',
            'checked_at' => '2026-08-30 08:00:00',
        ]);
        $kiaStatus = PrintingStatus::query()->where('document', PrintingDocument::ChildIdentityCard->value)->sole();
        $kiaStatus->update([
            'is_available' => false,
            'note' => '<script>alert("status")</script> Pencetakan belum tersedia.',
            'source' => 'Operator pencetakan',
            'checked_at' => '2026-08-30 08:05:00',
        ]);

        foreach ([route('home'), route('services')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('Status pencetakan')
                ->assertSeeInOrder(['Pencetakan KTP-el', 'Ada', 'Pencetakan KIA', 'Tidak ada'])
                ->assertSee('Kantor Dukcapil Limboto')
                ->assertSee('Pencetakan belum tersedia.')
                ->assertDontSee('<script>alert("status")</script>', false);
        }

        $this->get(route('information'))
            ->assertOk()
            ->assertDontSee('Status pencetakan KTP-el dan KIA')
            ->assertDontSee('Pencetakan belum tersedia.');
    }

    public function test_services_page_displays_current_supply_statuses(): void
    {
        SupplyStatus::factory()->create([
            'location' => 'Mall Pelayanan Publik',
            'item' => 'Blangko KTP-el layanan publik',
            'status' => SupplyLevel::Limited,
            'note' => '<script>alert("supply")</script> Persediaan terbatas.',
            'source' => 'Operator bahan cetak',
            'checked_at' => '2026-08-30 09:15:00',
        ]);

        $response = $this->get(route('services'));

        $response
            ->assertSee('Status bahan cetak')
            ->assertSee('Blangko KTP-el layanan publik')
            ->assertSee('Mall Pelayanan Publik')
            ->assertSee('Terbatas')
            ->assertSee('Persediaan terbatas.')
            ->assertSee('Operator bahan cetak')
            ->assertDontSee('<script>alert("supply")</script>', false);
    }

    public function test_services_page_displays_empty_status_messages(): void
    {
        PrintingStatus::query()->delete();
        SupplyStatus::query()->delete();

        $response = $this->get(route('services'));

        $response
            ->assertSee('Status pencetakan belum tersedia.')
            ->assertSee('Status bahan cetak belum tersedia.');
    }

    public function test_csp_nonce_is_applied_to_every_rendered_script(): void
    {
        $this->withVite();
        $response = $this->get('/')->assertOk();
        $contentSecurityPolicy = $response->headers->get('Content-Security-Policy');

        $this->assertIsString($contentSecurityPolicy);
        $this->assertSame(1, preg_match("/'nonce-([^']+)'/", $contentSecurityPolicy, $nonceMatches));
        $this->assertGreaterThanOrEqual(1, preg_match_all('/<script\\b([^>]*)>/i', $response->getContent(), $scriptMatches));

        foreach ($scriptMatches[1] as $scriptAttributes) {
            $this->assertStringContainsString('nonce="'.$nonceMatches[1].'"', $scriptAttributes);
        }
    }

    public function test_cross_origin_opener_policy_is_only_sent_over_https(): void
    {
        $this->get('http://192.168.1.10/')
            ->assertOk()
            ->assertHeaderMissing('Cross-Origin-Opener-Policy')
            ->assertHeaderMissing('Strict-Transport-Security');

        $this->get('https://dukcapil.test/')
            ->assertOk()
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_draft_news_cannot_be_opened_publicly(): void
    {
        $draft = NewsArticle::factory()->create();

        $this->get(route('news.show', $draft))->assertNotFound();
    }

    public function test_main_public_pages_are_available(): void
    {
        foreach (['services', 'profile', 'information', 'documents', 'employees', 'employee-apps', 'contact', 'news.index'] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_services_page_displays_announcement_and_siprima_submission_flow(): void
    {
        $response = $this->get(route('services'));

        $response
            ->assertSee('Pengumuman perubahan layanan')
            ->assertSee('Mulai 1 Maret 2026')
            ->assertSee('src="'.asset('images/pengumuman-layanan-dukcapil.jpeg').'"', false)
            ->assertSee('alt="Poster pengumuman perubahan layanan Dukcapil Kabupaten Gorontalo mulai 1 Maret 2026"', false)
            ->assertSee('Alur pengajuan melalui SI PRIMA')
            ->assertSee('src="'.asset('images/alur-pengajuan-siprima.png').'"', false)
            ->assertSee('alt="Diagram alur pengajuan dan penerimaan dokumen melalui SI PRIMA"', false)
            ->assertSee('href="'.config('dukcapil.service_url').'" target="_blank" rel="noopener noreferrer"', false)
            ->assertSee('href="'.asset('images/alur-pengajuan-siprima.png').'" target="_blank" rel="noopener noreferrer"', false);

        $this->assertFileExists(public_path('images/pengumuman-layanan-dukcapil.jpeg'));
        $this->assertFileExists(public_path('images/alur-pengajuan-siprima.png'));
    }

    public function test_services_page_displays_siprima_hours_and_current_contact_email(): void
    {
        $response = $this->get(route('services'));

        $response
            ->assertSee('Jam Pelayanan SI PRIMA')
            ->assertSee('Senin–Jumat, 08.00–15.30 WITA')
            ->assertSee('dkpskabgor@gmail.com')
            ->assertDontSee('dukcapil@gorontalokab.go.id');
    }

    public function test_documents_page_displays_official_external_resources(): void
    {
        $this->seed(ContentSeeder::class);

        $response = $this->get(route('documents'));

        $response
            ->assertOk()
            ->assertSee('Maklumat Pelayanan')
            ->assertSee('href="https://dukcapil.kemendagri.go.id/download" target="_blank" rel="noopener noreferrer"', false)
            ->assertSee('href="https://drive.google.com/file/d/1FSR7yo19nL_hzwr54MSJMfDJHWs84549/view?usp=sharing" target="_blank" rel="noopener noreferrer"', false)
            ->assertSee('href="https://drive.google.com/file/d/16td7X5xeDbSf67oQrWIHiBGvJfKff4tW/view?usp=sharing" target="_blank" rel="noopener noreferrer"', false);
    }

    public function test_information_page_displays_published_gender_statistics_by_area_hierarchy(): void
    {
        $areas = [
            ['area_code' => '75.01', 'area' => 'GORONTALO', 'area_level' => AdministrativeAreaLevel::Regency, 'parent_area_code' => null, 'values' => [100, 110, 210]],
            ['area_code' => '75.01.01', 'area' => 'KECAMATAN ALFA', 'area_level' => AdministrativeAreaLevel::District, 'parent_area_code' => '75.01', 'values' => [60, 70, 130]],
            ['area_code' => '75.01.01.1001', 'area' => 'DESA ALFA', 'area_level' => AdministrativeAreaLevel::Village, 'parent_area_code' => '75.01.01', 'values' => [60, 70, 130]],
        ];
        $labels = [
            PopulationStatistic::LABEL_MALE,
            PopulationStatistic::LABEL_FEMALE,
            PopulationStatistic::LABEL_TOTAL,
        ];

        foreach ($areas as $area) {
            foreach ($labels as $index => $label) {
                PopulationStatistic::factory()->create([
                    'area_code' => $area['area_code'],
                    'area' => $area['area'],
                    'area_level' => $area['area_level'],
                    'parent_area_code' => $area['parent_area_code'],
                    'label' => $label,
                    'category' => PopulationStatistic::CATEGORY_GENDER,
                    'value' => $area['values'][$index],
                    'period' => 'Semester I 2026',
                    'published_at' => now()->subMinute(),
                ]);
            }
        }
        PopulationStatistic::factory()->create([
            'label' => PopulationStatistic::LABEL_TOTAL,
            'area' => 'KECAMATAN DRAF',
            'area_code' => '75.01.99',
            'area_level' => AdministrativeAreaLevel::District,
            'parent_area_code' => '75.01',
            'period' => 'Semester I 2026',
            'published_at' => null,
        ]);

        $response = $this->get(route('information', [
            'period' => 'Semester I 2026',
            'kecamatan' => '75.01.01',
        ]));

        $response
            ->assertOk()
            ->assertSee('210')
            ->assertSee('Komposisi kabupaten')
            ->assertSee('47,6%')
            ->assertSee('52,4%')
            ->assertSee('data-gender-composition-chart', false)
            ->assertSee('data-district-population-chart', false)
            ->assertSee('Maksimum: 130 jiwa')
            ->assertSee('aria-label="KECAMATAN ALFA: laki-laki 60 jiwa, perempuan 70 jiwa, jumlah 130 jiwa"', false)
            ->assertSee('KECAMATAN ALFA')
            ->assertSee('DESA ALFA')
            ->assertSee('75.01.01.1001')
            ->assertDontSee('KECAMATAN DRAF');
    }

    public function test_information_page_explains_when_gender_composition_is_empty(): void
    {
        foreach ([PopulationStatistic::LABEL_MALE, PopulationStatistic::LABEL_FEMALE, PopulationStatistic::LABEL_TOTAL] as $label) {
            PopulationStatistic::factory()->create([
                'area_code' => '75.01',
                'area' => 'GORONTALO',
                'area_level' => AdministrativeAreaLevel::Regency,
                'parent_area_code' => null,
                'label' => $label,
                'category' => PopulationStatistic::CATEGORY_GENDER,
                'value' => 0,
                'period' => 'Semester I 2026',
                'published_at' => now()->subMinute(),
            ]);
        }

        $this->get(route('information', ['period' => 'Semester I 2026']))
            ->assertOk()
            ->assertSee('Komposisi belum dapat dihitung karena nilai laki-laki dan perempuan masih kosong.')
            ->assertDontSee('stroke-dasharray=', false);
    }

    public function test_information_page_displays_exact_values_for_an_aggregate_category(): void
    {
        $values = [
            'Islam' => 100,
            'Kristen' => 10,
            'Katolik' => 3,
            'Hindu' => 0,
            'Buddha' => 0,
            'Khonghucu' => 0,
            'Kepercayaan' => 0,
        ];

        foreach (['75.01' => ['GORONTALO', AdministrativeAreaLevel::Regency], '75.01.01' => ['KECAMATAN ALFA', AdministrativeAreaLevel::District]] as $areaCode => [$area, $areaLevel]) {
            foreach ($values as $label => $value) {
                PopulationStatistic::factory()->create([
                    'area_code' => $areaCode,
                    'area' => $area,
                    'area_level' => $areaLevel,
                    'parent_area_code' => $areaLevel === AdministrativeAreaLevel::District ? '75.01' : null,
                    'label' => $label,
                    'category' => PopulationStatisticCategory::Religion->value,
                    'value' => $value,
                    'period' => 'Semester I 2026',
                    'published_at' => now()->subMinute(),
                ]);
            }
        }

        $response = $this->get(route('information', [
            'period' => 'Semester I 2026',
            'kategori' => PopulationStatisticCategory::Religion->slug(),
            'wilayah' => '75.01.01',
        ]));

        $response
            ->assertOk()
            ->assertSee('Penduduk menurut agama — KECAMATAN ALFA')
            ->assertSee('data-aggregate-category-chart', false)
            ->assertSee('Katolik: 3 jiwa')
            ->assertDontSee('Nilai dilindungi')
            ->assertDontSee('Disamarkan');
    }
}
