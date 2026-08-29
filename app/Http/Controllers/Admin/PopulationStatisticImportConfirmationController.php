<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ImportPopulationStatistics;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConfirmPopulationStatisticImportRequest;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;

class PopulationStatisticImportConfirmationController extends Controller
{
    public function store(
        ConfirmPopulationStatisticImportRequest $request,
        ImportPopulationStatistics $importPopulationStatistics,
        AuditLogger $auditLogger,
    ): RedirectResponse {
        $token = $request->string('token')->toString();
        $cacheKey = PopulationStatisticImportController::cacheKey($request->user()->id, $token);

        $result = Cache::lock($cacheKey.':lock', 10)->block(5, function () use ($request, $cacheKey, $importPopulationStatistics): ?array {
            $pendingImport = Cache::get($cacheKey);

            if (! is_array($pendingImport)) {
                return null;
            }

            $result = $importPopulationStatistics->handle(
                $pendingImport,
                $request->user(),
                $request->boolean('publish_now'),
            );
            Cache::forget($cacheKey);

            return $result;
        });

        if ($result === null) {
            return redirect()
                ->route('admin.population-statistic-imports.create')
                ->withErrors(['workbooks' => 'Pratinjau impor sudah kedaluwarsa. Silakan unggah ulang file Excel.']);
        }

        $auditLogger->record(
            'statistic.imported',
            "Data agregat {$result['period']} diimpor dari Excel.",
            properties: $result,
        );

        $publicationStatus = $result['published'] ? 'dan diterbitkan' : 'sebagai draf';

        return redirect()
            ->route('admin.population-statistics.index')
            ->with('status', "{$result['statistic_count']} statistik dari {$result['dataset_count']} kategori berhasil diimpor {$publicationStatus}.");
    }
}
