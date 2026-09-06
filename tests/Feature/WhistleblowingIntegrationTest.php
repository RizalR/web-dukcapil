<?php

namespace Tests\Feature;

use App\Models\WhistleblowingPublication;
use App\Models\WhistleblowingSyncState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhistleblowingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.siprima_whistleblowing.url', 'https://siprima.test/api/v1/whistleblowing-publications.php');
        config()->set('services.siprima_whistleblowing.token', str_repeat('a', 40));
        Http::preventStrayRequests();
    }

    public function test_sync_imports_only_the_public_snapshot_using_server_credentials(): void
    {
        Http::fake([
            'https://siprima.test/*' => Http::response($this->snapshot([$this->publication()]), 200),
        ]);

        $this->artisan('whistleblowing:sync')->assertSuccessful();

        $this->assertDatabaseHas('whistleblowing_publications', [
            'publication_id' => 'WBP-1234567890ABCDEF12345678',
            'category_code' => 'pelanggaran_prosedur',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('whistleblowing_sync_states', [
            'source' => 'siprima',
            'last_item_count' => 1,
            'last_error_code' => null,
        ]);
        Http::assertSent(fn (ClientRequest $request): bool => $request->url() === 'https://siprima.test/api/v1/whistleblowing-publications.php'
            && $request->hasHeader('Authorization', 'Bearer '.str_repeat('a', 40)));
    }

    public function test_complete_empty_snapshot_withdraws_previous_publications(): void
    {
        Http::fakeSequence()
            ->push($this->snapshot([$this->publication()]), 200)
            ->push($this->snapshot([]), 200);

        $this->artisan('whistleblowing:sync')->assertSuccessful();
        $this->artisan('whistleblowing:sync')->assertSuccessful();

        $publication = WhistleblowingPublication::query()->sole();
        $this->assertFalse($publication->is_active);
        $this->assertNotNull($publication->withdrawn_at);
        $this->assertSame(0, WhistleblowingSyncState::query()->where('source', 'siprima')->sole()->last_item_count);
    }

    public function test_invalid_snapshot_keeps_previous_publications_visible(): void
    {
        $publication = WhistleblowingPublication::factory()->create();
        $invalidSnapshot = $this->snapshot([$this->publication()]);
        $invalidSnapshot['data'][0]['pemohon_nik'] = '7501000000000001';
        Http::fake(['https://siprima.test/*' => Http::response($invalidSnapshot, 200)]);

        $this->artisan('whistleblowing:sync')->assertFailed();

        $this->assertTrue($publication->fresh()->is_active);
        $this->assertSame(
            'InvalidWhistleblowingSnapshot',
            WhistleblowingSyncState::query()->where('source', 'siprima')->sole()->last_error_code,
        );
    }

    public function test_snapshot_with_sensitive_text_is_rejected(): void
    {
        $publication = WhistleblowingPublication::factory()->create();
        $sensitivePublication = $this->publication();
        $sensitivePublication['summary'] = 'Ringkasan ini masih memuat NIK 7501000000000001 sehingga tidak boleh dipublikasikan.';
        Http::fake(['https://siprima.test/*' => Http::response($this->snapshot([$sensitivePublication]), 200)]);

        $this->artisan('whistleblowing:sync')->assertFailed();

        $this->assertTrue($publication->fresh()->is_active);
        $this->assertDatabaseCount('whistleblowing_publications', 1);
    }

    public function test_public_page_displays_official_complaint_channels_and_infographic(): void
    {
        $response = $this->get(route('whistleblowing.index'));

        $response
            ->assertSee('0811434356')
            ->assertSee('href="https://wa.me/62811434356" target="_blank" rel="noopener noreferrer"', false)
            ->assertDontSee('081243425557')
            ->assertDontSee('href="https://wa.me/6281243425557"', false)
            ->assertSee('href="'.config('dukcapil.complaints.siprima_whistleblowing_url').'" target="_blank" rel="noopener noreferrer"', false)
            ->assertSee('href="https://siprima.gorontalokab.go.id/informasi_whistleblowing.php" target="_blank" rel="noopener noreferrer"', false)
            ->assertSee('href="https://www.lapor.go.id/" target="_blank" rel="noopener noreferrer"', false)
            ->assertSee('src="'.asset('images/alur-pengaduan-whistleblowing.png').'"', false)
            ->assertSee('alt="Diagram sebelas langkah alur pengaduan whistleblowing SI PRIMA, mulai dari membuka aplikasi hingga publikasi hasil secara anonim"', false)
            ->assertDontSee('aria-hidden="true">WA</span>', false)
            ->assertDontSee('aria-hidden="true">1</span>', false)
            ->assertDontSee('aria-hidden="true">2</span>', false)
            ->assertDontSee('aria-hidden="true">!</span>', false)
            ->assertDontSee('Data belum pernah disinkronkan');

        $content = $response->getContent();
        $infographicPosition = strpos($content, 'images/alur-pengaduan-whistleblowing.png');
        $complaintNumberPosition = strpos($content, '0811434356');

        $this->assertIsInt($infographicPosition);
        $this->assertIsInt($complaintNumberPosition);
        $this->assertLessThan($complaintNumberPosition, $infographicPosition);
        $this->assertGreaterThanOrEqual(4, substr_count($content, '<svg'));
        $this->assertFileExists(public_path('images/alur-pengaduan-whistleblowing.png'));
    }

    /**
     * @param  list<array<string, string>>  $items
     * @return array<string, mixed>
     */
    private function snapshot(array $items): array
    {
        return [
            'schema_version' => 1,
            'generated_at' => '2026-09-02T20:00:00+08:00',
            'data' => $items,
            'meta' => [
                'count' => count($items),
                'complete' => true,
            ],
        ];
    }

    /** @return array<string, string> */
    private function publication(): array
    {
        return [
            'publication_id' => 'WBP-1234567890ABCDEF12345678',
            'category_code' => 'pelanggaran_prosedur',
            'category_label' => 'Pelanggaran Prosedur',
            'summary' => 'Ringkasan anonim mengenai pelanggaran prosedur pelayanan yang telah ditangani.',
            'follow_up_result' => 'Dukcapil telah mengevaluasi proses dan memperbaiki prosedur pelayanan terkait.',
            'published_at' => '2026-09-02T16:30:00+08:00',
            'updated_at' => '2026-09-02T17:00:00+08:00',
        ];
    }
}
