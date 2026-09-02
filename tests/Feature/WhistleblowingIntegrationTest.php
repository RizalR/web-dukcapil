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

    public function test_public_page_displays_only_active_records_and_escapes_imported_text(): void
    {
        WhistleblowingSyncState::query()->create([
            'source' => 'siprima',
            'last_succeeded_at' => now(),
            'last_item_count' => 2,
        ]);
        $visible = WhistleblowingPublication::factory()->create([
            'category_code' => 'diskriminasi',
            'category_label' => 'Diskriminasi Pelayanan',
            'summary' => '<script>alert("ringkasan")</script> Ringkasan anonim yang sudah disetujui untuk publikasi.',
            'follow_up_result' => 'Petugas telah melakukan evaluasi dan perbaikan prosedur pelayanan kepada masyarakat.',
        ]);
        $withdrawn = WhistleblowingPublication::factory()->withdrawn()->create([
            'summary' => 'Publikasi yang sudah ditarik dari SI PRIMA.',
        ]);

        $response = $this->get(route('whistleblowing.index', ['kategori' => 'diskriminasi']));

        $response
            ->assertOk()
            ->assertSee($visible->publication_id)
            ->assertSee('Diskriminasi Pelayanan')
            ->assertSee('&lt;script&gt;alert(&quot;ringkasan&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("ringkasan")</script>', false)
            ->assertDontSee($withdrawn->publication_id)
            ->assertSee('Terakhir diperbarui');
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
