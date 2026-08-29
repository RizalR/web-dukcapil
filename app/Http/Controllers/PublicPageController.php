<?php

namespace App\Http\Controllers;

use App\AdministrativeAreaLevel;
use App\LinkCategory;
use App\Models\Employee;
use App\Models\ExternalLink;
use App\Models\PopulationStatistic;
use App\Models\PrintingStatus;
use App\Models\PublicDocument;
use App\PopulationStatisticCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PublicPageController extends Controller
{
    public function services(): View
    {
        return view('public.services');
    }

    public function profile(): View
    {
        return view('public.profile');
    }

    public function information(Request $request): View
    {
        $areaLevels = [
            AdministrativeAreaLevel::Regency->value,
            AdministrativeAreaLevel::District->value,
            AdministrativeAreaLevel::Village->value,
        ];
        $periods = PopulationStatistic::query()
            ->published()
            ->select('period')
            ->selectRaw('MAX(published_at) as latest_publication')
            ->groupBy('period')
            ->orderByDesc('latest_publication')
            ->pluck('period');
        $requestedPeriod = $request->string('period')->toString();
        $selectedPeriod = $periods->contains($requestedPeriod) ? $requestedPeriod : $periods->first();
        $availableCategoryValues = $selectedPeriod === null
            ? collect()
            : PopulationStatistic::query()
                ->published()
                ->where('period', $selectedPeriod)
                ->distinct()
                ->pluck('category');
        $categories = collect(PopulationStatisticCategory::cases())
            ->filter(fn (PopulationStatisticCategory $category): bool => $availableCategoryValues->contains($category->value))
            ->values();
        $requestedCategory = PopulationStatisticCategory::fromSlug($request->string('kategori')->toString());
        $selectedCategory = $requestedCategory !== null && $categories->contains($requestedCategory)
            ? $requestedCategory
            : ($categories->firstWhere('value', PopulationStatisticCategory::Gender->value) ?? $categories->first());
        $statistics = $selectedPeriod === null
            ? collect()
            : PopulationStatistic::query()
                ->published()
                ->where('period', $selectedPeriod)
                ->when($selectedCategory !== null, fn ($query) => $query->where('category', $selectedCategory->value))
                ->whereIn('area_level', $areaLevels)
                ->orderBy('area_code')
                ->get();
        $areas = $statistics
            ->filter(fn (PopulationStatistic $statistic): bool => in_array($statistic->area_level, [AdministrativeAreaLevel::Regency, AdministrativeAreaLevel::District], true))
            ->unique('area_code')
            ->sortBy(fn (PopulationStatistic $statistic): string => ($statistic->area_level === AdministrativeAreaLevel::Regency ? '0' : '1').$statistic->area)
            ->map(fn (PopulationStatistic $statistic): array => [
                'area_code' => $statistic->area_code,
                'area' => $statistic->area,
                'area_level' => $statistic->area_level,
            ])
            ->values();
        $requestedAreaCode = $request->string('wilayah')->toString();
        $selectedArea = $areas->firstWhere('area_code', $requestedAreaCode)
            ?? $areas->firstWhere('area_level', AdministrativeAreaLevel::Regency)
            ?? $areas->first();
        $selectedAreaStatistics = $selectedArea === null
            ? collect()
            : $statistics->where('area_code', $selectedArea['area_code'])->values();
        $categoryChartRows = $selectedCategory === null
            ? collect()
            : $this->categoryChartRows($selectedAreaStatistics, $selectedCategory);
        $headlineValue = $selectedCategory?->headlineLabel() === null
            ? $selectedAreaStatistics->sum('value')
            : ($selectedAreaStatistics->firstWhere('label', $selectedCategory->headlineLabel())?->value ?? 0);
        $categorySource = $selectedAreaStatistics->first();
        $regency = $selectedCategory === PopulationStatisticCategory::Gender
            ? $this->areaRows($statistics, AdministrativeAreaLevel::Regency)->first()
            : null;
        $districts = $selectedCategory === PopulationStatisticCategory::Gender
            ? $this->areaRows($statistics, AdministrativeAreaLevel::District)->sortByDesc('total')->values()
            : collect();
        $districtChartMaximum = max(1, (int) ($districts->max(
            fn (array $district): int => max($district['total'], $district['male'] + $district['female'])
        ) ?? 0));
        $genderComposition = $this->genderComposition($regency);
        $requestedDistrictCode = $request->string('kecamatan')->toString();
        $selectedDistrict = $districts->firstWhere('area_code', $requestedDistrictCode);
        $villages = $selectedDistrict === null
            ? collect()
            : $this->areaRows($statistics, AdministrativeAreaLevel::Village)
                ->where('parent_area_code', $selectedDistrict['area_code'])
                ->sortByDesc('total')
                ->values();
        $legacyStatistics = $selectedPeriod === null
            ? collect()
            : PopulationStatistic::query()
                ->published()
                ->where('period', $selectedPeriod)
                ->where(function ($query): void {
                    $query->whereNull('area_level')->orWhere('area_level', AdministrativeAreaLevel::Legacy->value);
                })
                ->orderBy('area')
                ->orderBy('label')
                ->get();

        return view('public.information', [
            'periods' => $periods,
            'selectedPeriod' => $selectedPeriod,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'areas' => $areas,
            'selectedArea' => $selectedArea,
            'categoryChartRows' => $categoryChartRows,
            'headlineValue' => $headlineValue,
            'categorySource' => $categorySource,
            'regency' => $regency,
            'genderComposition' => $genderComposition,
            'districts' => $districts,
            'districtChartMaximum' => $districtChartMaximum,
            'selectedDistrict' => $selectedDistrict,
            'villages' => $villages,
            'legacyStatistics' => $legacyStatistics,
            'printingStatuses' => PrintingStatus::query()->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    /**
     * @param  Collection<int, PopulationStatistic>  $statistics
     * @return Collection<int, array{label: string, value: int, display_value: string, bar_percentage: float}>
     */
    private function categoryChartRows(Collection $statistics, PopulationStatisticCategory $category): Collection
    {
        $statisticsByLabel = $statistics->keyBy('label');
        $rows = collect($category->labels())
            ->map(function (string $label) use ($statisticsByLabel): array {
                $value = $statisticsByLabel->get($label)?->value ?? 0;

                return [
                    'label' => $label,
                    'value' => $value,
                    'display_value' => number_format($value, 0, ',', '.'),
                    'bar_percentage' => 0.0,
                ];
            });

        $maximumValue = max(1, (int) ($rows->max('value') ?? 0));

        return $rows->map(function (array $row) use ($maximumValue): array {
            $row['bar_percentage'] = round(($row['value'] / $maximumValue) * 100, 4);

            return $row;
        });
    }

    /**
     * @param  array{male: int, female: int}|null  $regency
     * @return array{male_percentage: float, female_percentage: float}|null
     */
    private function genderComposition(?array $regency): ?array
    {
        if ($regency === null) {
            return null;
        }

        $populationWithKnownGender = $regency['male'] + $regency['female'];

        if ($populationWithKnownGender === 0) {
            return null;
        }

        $malePercentage = round(($regency['male'] / $populationWithKnownGender) * 100, 1);

        return [
            'male_percentage' => $malePercentage,
            'female_percentage' => round(100 - $malePercentage, 1),
        ];
    }

    /**
     * @param  Collection<int, PopulationStatistic>  $statistics
     * @return Collection<int, array{area_code: string, area: string, area_level: AdministrativeAreaLevel, parent_area_code: string|null, male: int, female: int, total: int, source: string, source_url: string|null, published_at: mixed}>
     */
    private function areaRows(Collection $statistics, AdministrativeAreaLevel $areaLevel): Collection
    {
        return $statistics
            ->filter(fn (PopulationStatistic $statistic): bool => $statistic->area_level === $areaLevel)
            ->groupBy('area_code')
            ->map(function (Collection $areaStatistics): array {
                /** @var PopulationStatistic $first */
                $first = $areaStatistics->first();
                $byLabel = $areaStatistics->keyBy('label');

                return [
                    'area_code' => $first->area_code,
                    'area' => $first->area,
                    'area_level' => $first->area_level,
                    'parent_area_code' => $first->parent_area_code,
                    'male' => $byLabel->get(PopulationStatistic::LABEL_MALE)?->value ?? 0,
                    'female' => $byLabel->get(PopulationStatistic::LABEL_FEMALE)?->value ?? 0,
                    'total' => $byLabel->get(PopulationStatistic::LABEL_TOTAL)?->value ?? 0,
                    'source' => $first->source,
                    'source_url' => $first->source_url,
                    'published_at' => $first->published_at,
                ];
            })
            ->values();
    }

    public function documents(): View
    {
        return view('public.documents', [
            'documents' => PublicDocument::query()->published()->orderByDesc('year')->orderBy('title')->get()->groupBy('category'),
        ]);
    }

    public function employees(): View
    {
        return view('public.employees', [
            'employees' => Employee::query()->where('is_published', true)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function contact(): View
    {
        return view('public.contact');
    }

    public function employeeApps(): View
    {
        return view('public.employee-apps', [
            'links' => ExternalLink::query()->where('category', LinkCategory::EmployeeApp)->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }
}
