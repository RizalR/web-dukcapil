<?php

namespace App\Actions;

use App\Exceptions\InvalidPopulationStatisticWorkbook;
use App\PopulationStatisticCategory;
use App\Services\AggregateStatisticWorkbookParser;

class ImportAggregateStatisticsFromDirectory
{
    public function __construct(
        private readonly AggregateStatisticWorkbookParser $parser,
        private readonly ImportPopulationStatistics $importPopulationStatistics,
    ) {}

    /**
     * @return array{period: string, dataset_count: int, area_count: int, statistic_count: int, published: bool, datasets: list<array{category: string, area_count: int, statistic_count: int}>}
     */
    public function handle(string $directory, string $period, string $source, bool $publish): array
    {
        $datasets = [];

        foreach (PopulationStatisticCategory::cases() as $category) {
            $profile = $this->parser->profile($category);
            $path = $directory.DIRECTORY_SEPARATOR.$profile['default_filename'];

            if (! is_file($path)) {
                throw new InvalidPopulationStatisticWorkbook("Workbook {$profile['default_filename']} tidak ditemukan.");
            }

            $datasets[] = $this->parser->parse($path, $category, $profile['default_filename']);
        }

        return $this->importPopulationStatistics->handle([
            'period' => $period,
            'source' => $source,
            'source_url' => null,
            'datasets' => $datasets,
        ], null, $publish);
    }
}
