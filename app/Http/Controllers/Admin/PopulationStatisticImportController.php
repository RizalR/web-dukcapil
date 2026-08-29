<?php

namespace App\Http\Controllers\Admin;

use App\Actions\PreparePopulationStatisticImport;
use App\Exceptions\InvalidPopulationStatisticWorkbook;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PreviewPopulationStatisticImportRequest;
use App\Models\PopulationStatistic;
use App\PopulationStatisticCategory;
use App\Services\AggregateStatisticWorkbookParser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PopulationStatisticImportController extends Controller
{
    public function create(AggregateStatisticWorkbookParser $parser): View
    {
        $this->authorize('create', PopulationStatistic::class);

        return view('admin.statistics.import', [
            'uploadProfiles' => $parser->uploadProfiles(),
        ]);
    }

    public function store(
        PreviewPopulationStatisticImportRequest $request,
        AggregateStatisticWorkbookParser $parser,
        PreparePopulationStatisticImport $prepareImport,
    ): View {
        $datasets = [];

        foreach (PopulationStatisticCategory::cases() as $category) {
            $field = "workbooks.{$category->slug()}";
            $workbook = $request->file($field);

            if (! $workbook instanceof UploadedFile) {
                continue;
            }

            $filename = $this->safeFilename($workbook);

            try {
                $datasets[] = $parser->parse($workbook->getPathname(), $category, $filename);
            } catch (InvalidPopulationStatisticWorkbook $exception) {
                throw ValidationException::withMessages([$field => $exception->getMessage()]);
            }
        }

        $token = Str::random(40);
        $pendingImport = $prepareImport->handle(
            $request->safe()->only(['period', 'source', 'source_url']),
            $datasets,
        );

        Cache::put(self::cacheKey($request->user()->id, $token), $pendingImport, now()->addMinutes(30));

        return view('admin.statistics.import-preview', [
            'pendingImport' => $pendingImport,
            'token' => $token,
        ]);
    }

    public static function cacheKey(int $userId, string $token): string
    {
        return "population-statistic-import:{$userId}:{$token}";
    }

    private function safeFilename(UploadedFile $workbook): string
    {
        $filename = basename(str_replace('\\', '/', $workbook->getClientOriginalName()));

        return Str::limit($filename !== '' ? $filename : 'workbook.xlsx', 100, '');
    }
}
