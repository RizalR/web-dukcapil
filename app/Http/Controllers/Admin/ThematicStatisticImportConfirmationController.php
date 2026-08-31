<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ImportThematicStatistics;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConfirmThematicStatisticImportRequest;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;

class ThematicStatisticImportConfirmationController extends Controller
{
    public function store(
        ConfirmThematicStatisticImportRequest $request,
        ImportThematicStatistics $import,
        AuditLogger $auditLogger,
    ): RedirectResponse {
        $token = $request->string('token')->toString();
        $cacheKey = ThematicStatisticImportController::cacheKey((int) $request->user()->id, $token);
        $pendingImport = Cache::get($cacheKey);

        if (! is_array($pendingImport)) {
            return redirect()
                ->route('admin.thematic-statistic-imports.create')
                ->withErrors(['workbooks' => 'Pratinjau impor sudah kedaluwarsa. Silakan unggah ulang workbook.']);
        }

        $publishNow = $request->boolean('publish_now');
        $datasets = $import->handle($pendingImport, $request->user(), $publishNow);

        foreach ($datasets as $dataset) {
            $auditLogger->record(
                'thematic-statistic.imported',
                "Dataset {$dataset->type->title()} periode {$dataset->period} diimpor.",
                $dataset,
                [
                    'status' => $dataset->status->value,
                    'observation_count' => $dataset->observation_count,
                    'file_hash' => $dataset->file_hash,
                ],
            );
        }

        Cache::forget($cacheKey);
        $publicationStatus = $publishNow ? 'dan diterbitkan' : 'sebagai draf';

        return redirect()
            ->route('admin.thematic-statistic-datasets.index')
            ->with('status', count($datasets)." dataset berhasil diimpor {$publicationStatus}.");
    }
}
