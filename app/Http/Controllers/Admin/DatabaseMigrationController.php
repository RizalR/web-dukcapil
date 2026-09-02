<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Throwable;

class DatabaseMigrationController extends Controller
{
    public function create(): View
    {
        $this->authorizeAccess();

        return view('admin.database-migrations.create');
    }

    public function store(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorizeAccess();

        $request->validate(
            ['confirmation' => ['required', 'in:JALANKAN MIGRASI']],
            [
                'confirmation.required' => 'Ketik JALANKAN MIGRASI untuk melanjutkan.',
                'confirmation.in' => 'Teks konfirmasi tidak sesuai.',
            ],
        );

        try {
            $exitCode = Artisan::call('migrate', [
                '--force' => true,
                '--no-interaction' => true,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'migration' => 'Migrasi gagal dijalankan. Periksa storage/logs/laravel.log melalui File Manager cPanel.',
            ]);
        }

        if ($exitCode !== 0) {
            return back()->withErrors([
                'migration' => 'Migrasi selesai dengan status gagal. Periksa storage/logs/laravel.log melalui File Manager cPanel.',
            ]);
        }

        $auditLogger->record(
            'system.database-migrations.run',
            'Migrasi basis data dijalankan melalui panel Super Admin.',
        );

        return redirect()->route('admin.database-migrations.create')
            ->with('status', 'Migrasi basis data berhasil dijalankan. Nonaktifkan DUKCAPIL_WEB_MIGRATIONS_ENABLED di .env sekarang.');
    }

    private function authorizeAccess(): void
    {
        abort_unless(config('dukcapil.maintenance.web_migrations_enabled'), 404);
        Gate::authorize('runDatabaseMigrations');
    }
}
