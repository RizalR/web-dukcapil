<?php

namespace App\Services;

use App\AdministrativeAreaLevel;
use App\Exceptions\InvalidPopulationStatisticWorkbook;
use App\PopulationStatisticCategory;
use App\Support\AdministrativeAreaCodeNormalizer;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AggregateStatisticWorkbookParser
{
    public function __construct(
        private readonly PopulationStatisticXlsxReader $reader,
        private readonly AdministrativeAreaCodeNormalizer $areaCodeNormalizer,
    ) {}

    /**
     * @return list<array{slug: string, category: string, title: string, description: string, sheet: string, default_filename: string}>
     */
    public function uploadProfiles(): array
    {
        return array_map(
            fn (array $profile): array => [
                'slug' => $profile['category']->slug(),
                'category' => $profile['category']->value,
                'title' => $profile['category']->title(),
                'description' => $profile['category']->description(),
                'sheet' => $profile['sheet'],
                'default_filename' => $profile['default_filename'],
            ],
            $this->profiles(),
        );
    }

    /**
     * @return array{category: string, filename: string, area_count: int, statistic_count: int, rows: list<array{area_code: string, area: string, area_level: string, parent_area_code: string|null, values: array<string, int>}>}
     */
    public function parse(string $path, PopulationStatisticCategory $category, string $filename): array
    {
        $profile = collect($this->profiles())->first(
            fn (array $candidate): bool => $candidate['category'] === $category,
        );

        if (! is_array($profile)) {
            throw new InvalidArgumentException("Profil kategori {$category->value} tidak tersedia.");
        }

        $profile['filename'] = $filename;
        $areaRows = $this->parseAreaRows(
            $this->reader->readTabularFile($path, $profile['sheet']),
            $profile,
        );
        $this->assertDistrictRollupMatches($areaRows, $category);

        return [
            'category' => $category->value,
            'filename' => $filename,
            'area_count' => count($areaRows),
            'statistic_count' => collect($areaRows)->sum(fn (array $row): int => count($row['values'])),
            'rows' => $areaRows,
        ];
    }

    /** @return array{category: PopulationStatisticCategory, sheet: string, default_filename: string, include_villages: bool, metrics: array<string, list<string>>} */
    public function profile(PopulationStatisticCategory $category): array
    {
        $profile = collect($this->profiles())->first(
            fn (array $candidate): bool => $candidate['category'] === $category,
        );

        if (! is_array($profile)) {
            throw new InvalidArgumentException("Profil kategori {$category->value} tidak tersedia.");
        }

        return $profile;
    }

    /**
     * @param  list<array<string, string|null>>  $worksheetRows
     * @param  array{filename: string, sheet: string, category: PopulationStatisticCategory, include_villages: bool, metrics: array<string, list<string>>}  $profile
     * @return list<array{area_code: string, area: string, area_level: string, parent_area_code: string|null, values: array<string, int>}>
     */
    private function parseAreaRows(array $worksheetRows, array $profile): array
    {
        $headerRow = array_shift($worksheetRows);

        if ($headerRow === null) {
            throw new InvalidPopulationStatisticWorkbook("Workbook {$profile['filename']} tidak memiliki header.");
        }

        $columnsByHeader = [];

        foreach ($headerRow as $column => $header) {
            $normalizedHeader = $this->normalizeHeader($header);

            if ($normalizedHeader !== '') {
                $columnsByHeader[$normalizedHeader] = $column;
            }
        }

        $codeColumn = $columnsByHeader['KODE'] ?? null;
        $areaColumn = $columnsByHeader['WILAYAH'] ?? $columnsByHeader['WIL'] ?? null;

        if ($codeColumn === null || $areaColumn === null) {
            throw new InvalidPopulationStatisticWorkbook("Workbook {$profile['filename']} tidak memiliki kolom KODE dan WILAYAH.");
        }

        $metricColumns = [];

        foreach ($profile['metrics'] as $label => $headers) {
            foreach ($headers as $header) {
                $column = $columnsByHeader[$this->normalizeHeader($header)] ?? null;

                if ($column === null) {
                    throw new InvalidPopulationStatisticWorkbook("Kolom {$header} tidak ditemukan pada workbook {$profile['filename']}.");
                }

                $metricColumns[$label][] = $column;
            }
        }

        $areaRows = [];

        foreach ($worksheetRows as $index => $row) {
            $rowNumber = $index + 2;
            $areaCode = $this->areaCodeNormalizer->normalize((string) ($row[$codeColumn] ?? ''));
            $area = Str::squish((string) ($row[$areaColumn] ?? ''));

            if ($areaCode === '' && $area === '') {
                continue;
            }

            $areaLevel = $this->areaLevel($areaCode, $rowNumber, $profile['filename']);

            if ($areaLevel === AdministrativeAreaLevel::Village && ! $profile['include_villages']) {
                continue;
            }

            if ($area === '') {
                throw new InvalidPopulationStatisticWorkbook("Nama wilayah kosong pada baris {$rowNumber} di workbook {$profile['filename']}.");
            }

            $values = [];

            foreach ($metricColumns as $label => $columns) {
                $value = 0;

                foreach ($columns as $column) {
                    $value += $this->nonNegativeInteger($row[$column] ?? null, $rowNumber, $column, $profile['filename']);
                }

                $values[$label] = $value;
            }

            $areaRows[] = [
                'area_code' => $areaCode,
                'area' => $area,
                'area_level' => $areaLevel->value,
                'parent_area_code' => $this->parentAreaCode($areaCode, $areaLevel),
                'values' => $values,
            ];
        }

        if (collect($areaRows)->where('area_level', AdministrativeAreaLevel::Regency->value)->count() !== 1
            || collect($areaRows)->where('area_level', AdministrativeAreaLevel::District->value)->isEmpty()) {
            throw new InvalidPopulationStatisticWorkbook("Workbook {$profile['filename']} harus memuat satu kabupaten dan sedikitnya satu kecamatan.");
        }

        return $areaRows;
    }

    /**
     * @param  list<array{area_code: string, area: string, area_level: string, parent_area_code: string|null, values: array<string, int>}>  $areaRows
     */
    private function assertDistrictRollupMatches(array $areaRows, PopulationStatisticCategory $category): void
    {
        $regency = collect($areaRows)->firstWhere('area_level', AdministrativeAreaLevel::Regency->value);
        $districts = collect($areaRows)->where('area_level', AdministrativeAreaLevel::District->value);

        if (! is_array($regency)) {
            throw new InvalidPopulationStatisticWorkbook("Total kabupaten untuk {$category->title()} tidak ditemukan.");
        }

        foreach ($regency['values'] as $label => $value) {
            if ($districts->sum(fn (array $district): int => $district['values'][$label]) !== $value) {
                throw new InvalidPopulationStatisticWorkbook("Jumlah kecamatan untuk {$label} tidak cocok dengan total kabupaten.");
            }
        }
    }

    private function areaLevel(string $areaCode, int $rowNumber, string $filename): AdministrativeAreaLevel
    {
        return match (true) {
            preg_match('/^\d{2}\.\d{2}$/', $areaCode) === 1 => AdministrativeAreaLevel::Regency,
            preg_match('/^\d{2}\.\d{2}\.\d{2}$/', $areaCode) === 1 => AdministrativeAreaLevel::District,
            preg_match('/^\d{2}\.\d{2}\.\d{2}\.\d{4}$/', $areaCode) === 1 => AdministrativeAreaLevel::Village,
            default => throw new InvalidPopulationStatisticWorkbook("Kode wilayah {$areaCode} pada baris {$rowNumber} di workbook {$filename} tidak valid."),
        };
    }

    private function parentAreaCode(string $areaCode, AdministrativeAreaLevel $areaLevel): ?string
    {
        return match ($areaLevel) {
            AdministrativeAreaLevel::Regency => null,
            AdministrativeAreaLevel::District => implode('.', array_slice(explode('.', $areaCode), 0, 2)),
            AdministrativeAreaLevel::Village => implode('.', array_slice(explode('.', $areaCode), 0, 3)),
            AdministrativeAreaLevel::Legacy => null,
        };
    }

    private function nonNegativeInteger(?string $value, int $rowNumber, string $column, string $filename): int
    {
        $value = trim((string) $value);

        if (preg_match('/^\d+$/', $value) !== 1 || strlen($value) > 10 || (int) $value > 1_000_000_000) {
            throw new InvalidPopulationStatisticWorkbook("Kolom {$column} pada baris {$rowNumber} di workbook {$filename} harus berupa bilangan bulat nonnegatif.");
        }

        return (int) $value;
    }

    private function normalizeHeader(?string $header): string
    {
        return Str::upper(trim((string) preg_replace('/\s+/u', ' ', (string) $header)));
    }

    /**
     * @return list<array{category: PopulationStatisticCategory, sheet: string, default_filename: string, include_villages: bool, metrics: array<string, list<string>>}>
     */
    private function profiles(): array
    {
        return [
            [
                'category' => PopulationStatisticCategory::Gender,
                'sheet' => 'Sheet1',
                'default_filename' => 'AGR_JK_VERT_202601_7501_GORONTALO.xlsx',
                'include_villages' => true,
                'metrics' => [
                    'Jumlah Penduduk Laki-laki' => ['L'],
                    'Jumlah Penduduk Perempuan' => ['P'],
                    'Jumlah Penduduk' => ['JML'],
                ],
            ],
            [
                'category' => PopulationStatisticCategory::HouseholdHead,
                'sheet' => 'Sheet1',
                'default_filename' => 'AGR_KEPALA_KELUARGA_VERT_202601_7501_GORONTALO.xlsx',
                'include_villages' => false,
                'metrics' => [
                    'Kepala Keluarga Laki-laki' => ['LK'],
                    'Kepala Keluarga Perempuan' => ['PR'],
                    'Jumlah Kepala Keluarga' => ['JML'],
                ],
            ],
            [
                'category' => PopulationStatisticCategory::AgeGroup,
                'sheet' => 'Sheet1',
                'default_filename' => 'AGR_JK_KEL_UMUR_VERT_202601_7501_GORONTALO.xlsx',
                'include_villages' => false,
                'metrics' => array_combine(
                    PopulationStatisticCategory::AgeGroup->labels(),
                    array_map(fn (string $age): array => ["JML {$age}"], ['00-04', '05-09', '10-14', '15-19', '20-24', '25-29', '30-34', '35-39', '40-44', '45-49', '50-54', '55-59', '60-64', '65-69', '70-74', '75+']),
                ),
            ],
            [
                'category' => PopulationStatisticCategory::Religion,
                'sheet' => 'DESA',
                'default_filename' => 'AGR_AGAMA_VERT_202601_7501_GORONTALO.xlsx',
                'include_villages' => false,
                'metrics' => [
                    'Islam' => ['ISLAM JML'],
                    'Kristen' => ['KRISTEN JML'],
                    'Katolik' => ['KATHOLIK JML'],
                    'Hindu' => ['HINDU JML'],
                    'Buddha' => ['BUDHA JML'],
                    'Khonghucu' => ['KHONGHUCU JML'],
                    'Kepercayaan' => ['KEPERCAYAAN JML'],
                ],
            ],
            [
                'category' => PopulationStatisticCategory::Education,
                'sheet' => 'Sheet1',
                'default_filename' => 'AGR_PDDKN_VERT_202601_7501_GORONTALO.xlsx',
                'include_villages' => false,
                'metrics' => [
                    'Tidak/belum sekolah' => ['TIDAK/BLM SEKOLAH JML'],
                    'Belum tamat SD/sederajat' => ['BELUM TAMAT SD/SEDERAJAT JML'],
                    'Tamat SD/sederajat' => ['TAMAT SD/SEDERAJAT JML'],
                    'SLTP/sederajat' => ['SLTP/SEDERAJAT JML'],
                    'SLTA/sederajat' => ['SLTA/SEDERAJAT JML'],
                    'Diploma I/II' => ['DIPLOMA I/II JML'],
                    'Akademi/Diploma III/Sarjana Muda' => ['AKADEMI/DIPL.III/S.MUDA JML'],
                    'Diploma IV/Strata I' => ['DIPLOMA IV/STRATA I JML'],
                    'Strata II' => ['STRATA-II JML'],
                    'Strata III' => ['STRATA-III JML'],
                ],
            ],
            [
                'category' => PopulationStatisticCategory::MaritalStatus,
                'sheet' => 'Sheet1',
                'default_filename' => 'AGR_STAT_KWN_VERT_202601_7501_GORONTALO.xlsx',
                'include_villages' => false,
                'metrics' => [
                    'Belum kawin' => ['L BELUM KAWIN', 'P BELUM KAWIN'],
                    'Kawin' => ['L KAWIN', 'P KAWIN'],
                    'Cerai hidup' => ['L CERAI HIDUP', 'P CERAI HIDUP'],
                    'Cerai mati' => ['L CERAI MATI', 'P CERAI MATI'],
                ],
            ],
            [
                'category' => PopulationStatisticCategory::Disability,
                'sheet' => 'Sheet1',
                'default_filename' => 'AGR_DISABILITAS_VERT_202601_7501_GORONTALO.xlsx',
                'include_villages' => false,
                'metrics' => [
                    'Disabilitas fisik' => ['DISABILITAS FISIK_JML'],
                    'Disabilitas netra/buta' => ['DISABILITAS NETRA/BUTA_JML'],
                    'Disabilitas rungu/wicara' => ['DISABILITAS RUNGU/WICARA_JML'],
                    'Disabilitas mental/jiwa' => ['DISABILITAS MENTAL/JIWA_JML'],
                    'Disabilitas fisik dan mental' => ['DISABILITAS FISIK DAN MENTAL_JML'],
                    'Disabilitas lainnya' => ['DISABILITAS LAINNYA_JML'],
                ],
            ],
            [
                'category' => PopulationStatisticCategory::ElectronicIdentityCard,
                'sheet' => 'Sheet1',
                'default_filename' => 'AGR_WKTP_REKAM_CETAK_202601_7501_GORONTALO.xlsx',
                'include_villages' => false,
                'metrics' => [
                    'Wajib KTP' => ['WKTP'],
                    'Sudah rekam' => ['REKAM'],
                    'Belum rekam' => ['BLM_REKAM'],
                    'Sudah memiliki KTP-el' => ['KTP'],
                    'Belum memiliki KTP-el' => ['BLMKTP'],
                ],
            ],
        ];
    }
}
