<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InvalidThematicStatisticWorkbook;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PreviewThematicStatisticImportRequest;
use App\Models\ThematicStatisticDataset;
use App\Services\ThematicStatisticWorkbookParser;
use App\ThematicStatisticType;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class ThematicStatisticImportController extends Controller
{
    public function create(ThematicStatisticWorkbookParser $parser): View
    {
        $this->authorize('create', ThematicStatisticDataset::class);

        return view('admin.thematic-statistics.import', [
            'uploadProfiles' => collect($parser->uploadProfiles())->groupBy('group'),
        ]);
    }

    public function store(
        PreviewThematicStatisticImportRequest $request,
        ThematicStatisticWorkbookParser $parser,
    ): View {
        $datasets = [];
        $validationErrors = [];
        $metadata = $request->safe()->only(['period', 'source', 'source_url']);

        foreach (ThematicStatisticType::cases() as $type) {
            $uploadedFile = $request->file('workbooks.'.$type->slug());

            if ($uploadedFile === null) {
                continue;
            }

            try {
                $temporaryPath = $uploadedFile->getRealPath();

                if ($temporaryPath === false) {
                    throw new InvalidThematicStatisticWorkbook('File unggahan sementara tidak dapat dibaca. Silakan unggah ulang.');
                }

                $dataset = $parser->parse(
                    $temporaryPath,
                    $type,
                    $uploadedFile->getClientOriginalName(),
                );

                if (ThematicStatisticDataset::query()
                    ->where('type', $type->value)
                    ->where('period', $metadata['period'])
                    ->where('file_hash', $dataset['file_hash'])
                    ->exists()) {
                    $validationErrors['workbooks.'.$type->slug()] = 'Workbook yang sama sudah pernah diimpor untuk periode ini.';

                    continue;
                }

                $datasets[] = $dataset;
            } catch (InvalidThematicStatisticWorkbook $exception) {
                $validationErrors['workbooks.'.$type->slug()] = $exception->getMessage();
            }
        }

        if ($validationErrors !== []) {
            throw ValidationException::withMessages($validationErrors);
        }

        $pendingImport = [
            ...$metadata,
            'dataset_count' => count($datasets),
            'area_count' => collect($datasets)
                ->flatMap(fn (array $dataset): array => array_column($dataset['observations'], 'area_code'))
                ->unique()
                ->count(),
            'row_count' => collect($datasets)->sum('row_count'),
            'observation_count' => collect($datasets)->sum('observation_count'),
            'datasets' => $datasets,
        ];
        $token = bin2hex(random_bytes(20));
        Cache::put(self::cacheKey((int) $request->user()->id, $token), $pendingImport, now()->addMinutes(30));

        return view('admin.thematic-statistics.import-preview', [
            'pendingImport' => $pendingImport,
            'token' => $token,
        ]);
    }

    public static function cacheKey(int $userId, string $token): string
    {
        return "thematic-statistic-import:{$userId}:{$token}";
    }
}
