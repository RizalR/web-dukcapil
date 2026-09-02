<?php

namespace App\Console\Commands;

use App\Actions\SyncWhistleblowingPublications as SyncWhistleblowingPublicationsAction;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('whistleblowing:sync')]
#[Description('Sinkronkan hasil whistleblowing publik dari SI PRIMA')]
class SyncWhistleblowingPublications extends Command
{
    public function handle(SyncWhistleblowingPublicationsAction $sync): int
    {
        try {
            $count = $sync->handle();
        } catch (Throwable $exception) {
            report($exception);
            $this->error('Sinkronisasi whistleblowing gagal. Data publik sebelumnya tetap dipertahankan.');

            return self::FAILURE;
        }

        $this->info("Sinkronisasi whistleblowing selesai: {$count} publikasi aktif.");

        return self::SUCCESS;
    }
}
