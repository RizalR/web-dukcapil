<?php

namespace App\Http\Controllers;

use App\Models\PopulationStatistic;
use App\PopulationStatisticCategory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StatisticsExportController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): StreamedResponse
    {
        $period = trim($request->string('period')->toString());
        $category = PopulationStatisticCategory::fromSlug($request->string('kategori')->toString());

        return response()->streamDownload(function () use ($period, $category): void {
            $stream = fopen('php://output', 'w');
            if ($stream === false) {
                return;
            }

            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['Indikator', 'Kategori', 'Nilai', 'Satuan', 'Periode', 'Kode Wilayah', 'Wilayah', 'Tingkat', 'Kode Induk', 'Sumber', 'Tanggal Publikasi']);

            PopulationStatistic::query()
                ->published()
                ->when($period !== '', fn ($query) => $query->where('period', $period))
                ->when($category !== null, fn ($query) => $query->where('category', $category->value))
                ->orderByDesc('published_at')
                ->orderBy('area_code')
                ->orderBy('label')
                ->each(function (PopulationStatistic $statistic) use ($stream): void {
                    fputcsv($stream, [
                        $this->csvSafe($statistic->label),
                        $this->csvSafe($statistic->category),
                        $statistic->value,
                        $this->csvSafe($statistic->unit),
                        $this->csvSafe($statistic->period),
                        $this->csvSafe($statistic->area_code ?? ''),
                        $this->csvSafe($statistic->area),
                        $this->csvSafe($statistic->area_level?->label() ?? 'Data umum'),
                        $this->csvSafe($statistic->parent_area_code ?? ''),
                        $this->csvSafe($statistic->source),
                        $statistic->published_at?->toDateString(),
                    ]);
                });

            fclose($stream);
        }, 'agregat-kependudukan.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function csvSafe(string $value): string
    {
        return preg_match('/^[=+\-@]/', $value) === 1 ? "'{$value}" : $value;
    }
}
