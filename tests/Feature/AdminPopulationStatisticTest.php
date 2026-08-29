<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPopulationStatisticTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_population_statistic(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->post(route('admin.population-statistics.store'), $this->payload())
            ->assertRedirect(route('admin.population-statistics.index'));

        $this->assertDatabaseHas('population_statistics', ['label' => 'Jumlah Penduduk', 'value' => 428370, 'updated_by' => $admin->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'statistic.created', 'user_id' => $admin->id]);
    }

    public function test_news_editor_cannot_access_population_statistics_admin(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)->get(route('admin.population-statistics.index'))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.population-statistics.store'), $this->payload())->assertForbidden();
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return [
            'label' => 'Jumlah Penduduk',
            'category' => 'Penduduk',
            'value' => 428370,
            'unit' => 'jiwa',
            'period' => 'Semester II 2025',
            'area' => 'Kabupaten Gorontalo',
            'source' => 'Dinas Dukcapil Kabupaten Gorontalo',
            'source_url' => 'https://dukcapil.gorontalokab.go.id/',
            'published_at' => '2026-02-12 08:00:00',
        ];
    }
}
