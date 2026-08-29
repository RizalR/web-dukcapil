<?php

namespace App\Console\Commands;

use App\Actions\ImportAggregateStatisticsFromDirectory;
use App\Exceptions\InvalidPopulationStatisticWorkbook;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('statistics:import-aggregate-directory
    {directory? : Folder sumber di bawah folder database}
    {--period=Semester I 2026 : Periode data}
    {--source=Dinas Dukcapil Kabupaten Gorontalo : Nama sumber data}
    {--publish : Terbitkan data segera setelah impor}')]
#[Description('Impor delapan dataset agregat inti dari direktori workbook Dukcapil')]
class ImportAggregateStatistics extends Command
{
    public function handle(ImportAggregateStatisticsFromDirectory $import): int
    {
        $directoryArgument = $this->argument('directory');
        $directory = is_string($directoryArgument) && $directoryArgument !== ''
            ? $directoryArgument
            : database_path('AGR 2026 SEM 1');
        $directory = $this->absolutePath($directory);
        $databaseDirectory = realpath(database_path());

        if ($directory === false
            || $databaseDirectory === false
            || ! is_dir($directory)
            || ! Str::startsWith(Str::lower($directory.DIRECTORY_SEPARATOR), Str::lower($databaseDirectory.DIRECTORY_SEPARATOR))) {
            $this->components->error('Direktori sumber harus berupa folder yang dapat dibaca di bawah folder database.');

            return self::FAILURE;
        }

        $period = trim((string) $this->option('period'));
        $source = trim((string) $this->option('source'));

        if ($period === '' || mb_strlen($period) > 80 || $source === '' || mb_strlen($source) > 100) {
            $this->components->error('Periode atau sumber data tidak valid.');

            return self::FAILURE;
        }

        try {
            $result = $import->handle($directory, $period, $source, (bool) $this->option('publish'));
        } catch (InvalidPopulationStatisticWorkbook $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Kategori', 'Wilayah', 'Nilai'],
            array_map(
                fn (array $dataset): array => [$dataset['category'], $dataset['area_count'], $dataset['statistic_count']],
                $result['datasets'],
            ),
        );
        $this->components->success(
            sprintf(
                '%d dataset, %d wilayah unik, dan %d nilai berhasil diimpor sebagai %s.',
                $result['dataset_count'],
                $result['area_count'],
                $result['statistic_count'],
                $result['published'] ? 'data terbit' : 'draf',
            ),
        );

        return self::SUCCESS;
    }

    private function absolutePath(string $path): string|false
    {
        if (preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1 || str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return realpath($path);
        }

        return realpath(base_path($path));
    }
}
