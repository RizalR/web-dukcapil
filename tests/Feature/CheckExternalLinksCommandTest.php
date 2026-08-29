<?php

namespace Tests\Feature;

use App\Models\ExternalLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckExternalLinksCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_records_successful_and_failed_link_checks(): void
    {
        $working = ExternalLink::factory()->create(['url' => 'https://example.test/working']);
        $broken = ExternalLink::factory()->create(['url' => 'https://example.test/broken']);
        Http::fake([
            'https://example.test/working' => Http::response('', 200),
            'https://example.test/broken' => Http::response('', 503),
        ]);

        $this->artisan('links:check')->assertFailed();

        $this->assertTrue($working->fresh()->last_check_ok);
        $this->assertFalse($broken->fresh()->last_check_ok);
        $this->assertSame(503, $broken->fresh()->last_status_code);
        $this->assertNotNull($working->fresh()->last_checked_at);
    }
}
