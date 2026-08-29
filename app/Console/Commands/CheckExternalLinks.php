<?php

namespace App\Console\Commands;

use App\Models\ExternalLink;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

#[Signature('links:check')]
#[Description('Periksa ketersediaan tautan eksternal yang aktif')]
class CheckExternalLinks extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $failed = 0;

        ExternalLink::query()->where('is_active', true)->orderBy('id')->each(function (ExternalLink $link) use (&$failed): void {
            try {
                $response = Http::connectTimeout(5)->timeout(10)->withHeaders(['User-Agent' => 'Dukcapil-Link-Checker/1.0'])->head($link->url);
                $ok = $response->successful() || $response->redirect();
                $link->update(['last_status_code' => $response->status(), 'last_check_ok' => $ok, 'last_checked_at' => now()]);
            } catch (Throwable) {
                $ok = false;
                $link->update(['last_status_code' => null, 'last_check_ok' => false, 'last_checked_at' => now()]);
            }

            if (! $ok) {
                $failed++;
                $this->warn("Gagal: {$link->name}");
            }
        });

        $this->info('Pemeriksaan tautan selesai.');

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
