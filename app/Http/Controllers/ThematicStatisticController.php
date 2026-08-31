<?php

namespace App\Http\Controllers;

use App\AdministrativeAreaLevel;
use App\Models\ThematicStatisticDataset;
use App\Models\ThematicStatisticObservation;
use App\ThematicStatisticType;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ThematicStatisticController extends Controller
{
    /** @var list<string> */
    private const array EDUCATION_ORDER = [
        'Tidak/belum sekolah',
        'Belum tamat SD/sederajat',
        'Tamat SD/sederajat',
        'SLTP/sederajat',
        'SLTA/sederajat',
        'Diploma I/II',
        'Akademi/Diploma III/Sarjana Muda',
        'Diploma IV/Strata I',
        'Strata II',
        'Strata III',
    ];

    public function __invoke(Request $request): View
    {
        $periods = ThematicStatisticDataset::query()
            ->published()
            ->select('period')
            ->selectRaw('MAX(published_at) as latest_published_at')
            ->groupBy('period')
            ->orderByDesc('latest_published_at')
            ->pluck('period');
        $requestedPeriod = $request->string('period')->trim()->toString();
        $selectedPeriod = $periods->contains($requestedPeriod) ? $requestedPeriod : $periods->first();

        if ($selectedPeriod === null) {
            return view('public.thematic-statistics', $this->emptyViewData($periods));
        }

        /** @var EloquentCollection<int, ThematicStatisticDataset> $datasets */
        $datasets = ThematicStatisticDataset::query()
            ->published()
            ->where('period', $selectedPeriod)
            ->with(['observations' => fn ($query) => $query->orderBy('area_code')->orderBy('id')])
            ->orderByDesc('published_at')
            ->get()
            ->unique(fn (ThematicStatisticDataset $dataset): string => $dataset->type->value)
            ->values();
        $datasetsByType = $datasets->keyBy(fn (ThematicStatisticDataset $dataset): string => $dataset->type->value);
        $areas = $this->areas($datasets);
        $requestedAreaCode = $request->string('wilayah')->trim()->toString();
        $selectedArea = $areas->firstWhere('area_code', $requestedAreaCode)
            ?? $areas->firstWhere('area_code', '75.01')
            ?? $areas->first();
        $selectedAreaCode = $selectedArea['area_code'] ?? null;

        return view('public.thematic-statistics', [
            'periods' => $periods,
            'selectedPeriod' => $selectedPeriod,
            'datasets' => $datasets,
            'areas' => $areas,
            'selectedArea' => $selectedArea,
            'apakHeadline' => $this->headline($datasetsByType->get(ThematicStatisticType::LaborForceParticipation->value), $selectedAreaCode),
            'apakByAge' => $this->participationBreakdown($datasetsByType->get(ThematicStatisticType::LaborForceParticipationByAge->value), $selectedAreaCode, true),
            'apakByEducation' => $this->participationBreakdown($datasetsByType->get(ThematicStatisticType::LaborForceParticipationByEducation->value), $selectedAreaCode),
            'apakByFamilyRelationship' => $this->participationBreakdown($datasetsByType->get(ThematicStatisticType::LaborForceParticipationByFamilyRelationship->value), $selectedAreaCode),
            'educationAgeRows' => $this->educationBySingleAge($datasetsByType->get(ThematicStatisticType::EducationBySingleAge->value)),
            'educationLevels' => self::EDUCATION_ORDER,
            'documentCards' => $this->documentCards($datasetsByType, $selectedAreaCode),
            'deathCertificate' => $this->deathCertificate($datasetsByType->get(ThematicStatisticType::DeathCertificate->value), $selectedAreaCode),
        ]);
    }

    /** @param Collection<int, string> $periods */
    private function emptyViewData(Collection $periods): array
    {
        return [
            'periods' => $periods,
            'selectedPeriod' => null,
            'datasets' => collect(),
            'areas' => collect(),
            'selectedArea' => null,
            'apakHeadline' => null,
            'apakByAge' => collect(),
            'apakByEducation' => collect(),
            'apakByFamilyRelationship' => collect(),
            'educationAgeRows' => collect(),
            'educationLevels' => self::EDUCATION_ORDER,
            'documentCards' => collect(),
            'deathCertificate' => null,
        ];
    }

    /**
     * @param  EloquentCollection<int, ThematicStatisticDataset>  $datasets
     * @return Collection<int, array{area_code: string, area: string, area_level: string}>
     */
    private function areas(EloquentCollection $datasets): Collection
    {
        return $datasets
            ->flatMap(fn (ThematicStatisticDataset $dataset): EloquentCollection => $dataset->observations)
            ->filter(fn (ThematicStatisticObservation $observation): bool => in_array($observation->area_level, [AdministrativeAreaLevel::Regency->value, AdministrativeAreaLevel::District->value], true))
            ->unique('area_code')
            ->sortBy(fn (ThematicStatisticObservation $observation): string => $observation->area_code)
            ->map(fn (ThematicStatisticObservation $observation): array => [
                'area_code' => $observation->area_code,
                'area' => $observation->area,
                'area_level' => $observation->area_level,
            ])
            ->values();
    }

    /** @return array<string, mixed>|null */
    private function headline(?ThematicStatisticDataset $dataset, ?string $areaCode): ?array
    {
        if ($dataset === null || $areaCode === null) {
            return null;
        }

        $observations = $dataset->observations
            ->where('area_code', $areaCode)
            ->where('dimension_one_value', '')
            ->keyBy(fn (ThematicStatisticObservation $observation): string => $observation->metric.'.'.$observation->sex);

        if (! $observations->has('participation_rate.total')) {
            return null;
        }

        return [
            'male' => $this->numericValue($observations->get('participation_rate.male')),
            'female' => $this->numericValue($observations->get('participation_rate.female')),
            'total' => $this->numericValue($observations->get('participation_rate.total')),
            'labor_force' => (int) $this->numericValue($observations->get('labor_force.total')),
            'population' => (int) $this->numericValue($observations->get('population.total')),
            'source' => $dataset->source,
            'published_at' => $dataset->published_at,
        ];
    }

    /** @return Collection<int, array{label: string, male: float, female: float, total: float}> */
    private function participationBreakdown(?ThematicStatisticDataset $dataset, ?string $areaCode, bool $sortNumerically = false): Collection
    {
        if ($dataset === null || $areaCode === null) {
            return collect();
        }

        $rows = $dataset->observations
            ->where('area_code', $areaCode)
            ->where('metric', 'participation_rate')
            ->groupBy('dimension_one_value')
            ->map(function (Collection $observations, string $label): array {
                $bySex = $observations->keyBy('sex');

                return [
                    'label' => $label,
                    'male' => $this->numericValue($bySex->get('male')),
                    'female' => $this->numericValue($bySex->get('female')),
                    'total' => $this->numericValue($bySex->get('total')),
                ];
            });

        return ($sortNumerically
            ? $rows->sortBy(fn (array $row): int => (int) preg_replace('/\D+.*$/', '', $row['label']))
            : $rows)
            ->values();
    }

    /** @return Collection<int, array{age: int, total: int, cells: array<string, array{value: int, percentage: float}>}> */
    private function educationBySingleAge(?ThematicStatisticDataset $dataset): Collection
    {
        if ($dataset === null) {
            return collect();
        }

        return $dataset->observations
            ->where('area_code', '75.01')
            ->where('metric', 'population')
            ->where('sex', 'total')
            ->groupBy('dimension_one_value')
            ->map(function (Collection $observations, string $age): array {
                $byEducation = $observations->keyBy('dimension_two_value');
                $total = (int) $observations->sum(fn (ThematicStatisticObservation $observation): float => (float) $observation->value);
                $cells = [];

                foreach (self::EDUCATION_ORDER as $education) {
                    $value = (int) $this->numericValue($byEducation->get($education));
                    $cells[$education] = [
                        'value' => $value,
                        'percentage' => $total === 0 ? 0.0 : round(($value / $total) * 100, 2),
                    ];
                }

                return ['age' => (int) $age, 'total' => $total, 'cells' => $cells];
            })
            ->sortBy('age')
            ->values();
    }

    /**
     * @param  Collection<string, ThematicStatisticDataset>  $datasetsByType
     * @return Collection<int, array<string, mixed>>
     */
    private function documentCards(Collection $datasetsByType, ?string $areaCode): Collection
    {
        if ($areaCode === null) {
            return collect();
        }

        $types = [
            ThematicStatisticType::BirthCertificateAgeZeroToOne,
            ThematicStatisticType::BirthCertificateAgeZeroToFive,
            ThematicStatisticType::BirthCertificateAgeZeroToEighteen,
            ThematicStatisticType::BirthCertificateAllAges,
            ThematicStatisticType::ChildIdentityCard,
        ];

        return collect($types)->map(function (ThematicStatisticType $type) use ($datasetsByType, $areaCode): ?array {
            $dataset = $datasetsByType->get($type->value);

            if ($dataset === null) {
                return null;
            }

            $observations = $dataset->observations
                ->where('area_code', $areaCode)
                ->where('sex', 'total')
                ->keyBy('metric');

            if (! $observations->has('target')) {
                return null;
            }

            return [
                'title' => $type->title(),
                'description' => $type->description(),
                'target' => (int) $this->numericValue($observations->get('target')),
                'owned' => (int) $this->numericValue($observations->get('owned')),
                'not_owned' => (int) $this->numericValue($observations->get('not_owned')),
                'coverage' => $this->numericValue($observations->get('coverage')),
                'source' => $dataset->source,
                'published_at' => $dataset->published_at,
            ];
        })->filter()->values();
    }

    /** @return array<string, mixed>|null */
    private function deathCertificate(?ThematicStatisticDataset $dataset, ?string $areaCode): ?array
    {
        if ($dataset === null || $areaCode === null) {
            return null;
        }

        $observations = $dataset->observations
            ->where('area_code', $areaCode)
            ->where('sex', 'total')
            ->keyBy('metric');

        if (! $observations->has('deaths')) {
            return null;
        }

        return [
            'issued' => (int) $this->numericValue($observations->get('issued')),
            'cancelled' => (int) $this->numericValue($observations->get('cancelled')),
            'deaths' => (int) $this->numericValue($observations->get('deaths')),
            'source' => $dataset->source,
            'published_at' => $dataset->published_at,
        ];
    }

    private function numericValue(?ThematicStatisticObservation $observation): float
    {
        return $observation === null ? 0.0 : (float) $observation->value;
    }
}
