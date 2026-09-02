<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DatabaseMigrationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_route_is_not_found_when_web_migrations_are_disabled(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.database-migrations.create'))
            ->assertNotFound();
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        config()->set('dukcapil.maintenance.web_migrations_enabled', true);

        $this->get(route('admin.database-migrations.create'))
            ->assertRedirect(route('login'));
    }

    public function test_news_editor_cannot_open_or_run_database_migrations(): void
    {
        config()->set('dukcapil.maintenance.web_migrations_enabled', true);
        $editor = User::factory()->create();

        $this->actingAs($editor)
            ->get(route('admin.database-migrations.create'))
            ->assertForbidden();
        $this->actingAs($editor)
            ->post(route('admin.database-migrations.store'), ['confirmation' => 'JALANKAN MIGRASI'])
            ->assertForbidden();
    }

    public function test_super_admin_can_open_database_migration_confirmation(): void
    {
        config()->set('dukcapil.maintenance.web_migrations_enabled', true);
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.database-migrations.create'))
            ->assertOk()
            ->assertSee('Migrasi database')
            ->assertSee('Migrasi basis data')
            ->assertSee('JALANKAN MIGRASI');
    }

    public function test_invalid_confirmation_does_not_run_database_migrations(): void
    {
        config()->set('dukcapil.maintenance.web_migrations_enabled', true);
        $admin = User::factory()->superAdmin()->create();
        Artisan::shouldReceive('call')->never();

        $this->actingAs($admin)
            ->from(route('admin.database-migrations.create'))
            ->post(route('admin.database-migrations.store'), ['confirmation' => 'ya'])
            ->assertRedirect(route('admin.database-migrations.create'))
            ->assertSessionHasErrors(['confirmation' => 'Teks konfirmasi tidak sesuai.']);
    }

    public function test_super_admin_can_run_database_migrations(): void
    {
        config()->set('dukcapil.maintenance.web_migrations_enabled', true);
        $admin = User::factory()->superAdmin()->create();
        Artisan::shouldReceive('call')
            ->once()
            ->with('migrate', [
                '--force' => true,
                '--no-interaction' => true,
            ])
            ->andReturn(0);

        $this->actingAs($admin)
            ->post(route('admin.database-migrations.store'), ['confirmation' => 'JALANKAN MIGRASI'])
            ->assertRedirect(route('admin.database-migrations.create'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'system.database-migrations.run',
        ]);
    }

    public function test_failed_database_migration_returns_a_safe_error_message(): void
    {
        config()->set('dukcapil.maintenance.web_migrations_enabled', true);
        $admin = User::factory()->superAdmin()->create();
        Artisan::shouldReceive('call')->once()->andReturn(1);

        $this->actingAs($admin)
            ->from(route('admin.database-migrations.create'))
            ->post(route('admin.database-migrations.store'), ['confirmation' => 'JALANKAN MIGRASI'])
            ->assertRedirect(route('admin.database-migrations.create'))
            ->assertSessionHasErrors([
                'migration' => 'Migrasi selesai dengan status gagal. Periksa storage/logs/laravel.log melalui File Manager cPanel.',
            ]);

        $this->assertDatabaseMissing('audit_logs', [
            'action' => 'system.database-migrations.run',
        ]);
    }
}
