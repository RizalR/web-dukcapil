<?php

namespace App\Services;

use App\AdministrativeAreaLevel;
use App\Exceptions\InvalidPopulationStatisticWorkbook;
use App\Exceptions\InvalidThematicStatisticWorkbook;
use App\Support\AdministrativeAreaCodeNormalizer;
use App\ThematicStatisticType;
use Illuminate\Support\Str;
use Throwable;

class ThematicStatisticWorkbookParser
{
    private const int MAX_XLSX_ROWS = 25_000;

    private const int MAX_XLSX_ENTRY_BYTES = 26_214_400;

    private const int MAX_XLSX_ARCHIVE_BYTES = 3_145_728;

    private const int MAX_XLSX_TOTAL_BYTES = 31_457_280;

    public function __construct(
        private readonly PopulationStatisticXlsxReader $xlsxReader,
        private readonly HtmlTableWorkbookReader $htmlReader,
        private readonly AdministrativeAreaCodeNormalizer $areaCodeNormalizer,
    ) {}

    /** @return list<array{slug: string, type: string, title: string, description: string, group: string, default_filename: string, extension: string}> */
    public function uploadProfiles(): array
    {
        return array_map(
            fn (ThematicStatisticType $type): array => [
                'slug' => $type->slug(),
                'type' => $type->value,
                'title' => $type->title(),
                'description' => $type->description(),
                'group' => $type->group(),
                'default_filename' => $type->defaultFilename(),
                'extension' => $type->isHtmlSpreadsheet() ? '.xls' : '.xlsx',
            ],
            ThematicStatisticType::cases(),
        );
    }

    /**
     * @return array{
     *     type: string,
     *     title: string,
     *     filename: string,
     *     file_hash: string,
     *     row_count: int,
     *     area_count: int,
     *     observation_count: int,
     *     warnings: list<string>,
     *     observations: list<array<string, string|null>>
     * }
     */
    public function parse(string $path, ThematicStatisticType $type, string $filename): array
    {
        $dataRowFilter = $type === ThematicStatisticType::EducationBySingleAge
            ? $this->regencyRowFilter()
            : null;

        try {
            $rows = $type->isHtmlSpreadsheet()
                ? $this->htmlReader->read($path)
                : $this->xlsxReader->readTabularFile(
                    $path,
                    'Sheet1',
                    self::MAX_XLSX_ROWS,
                    self::MAX_XLSX_ENTRY_BYTES,
                    self::MAX_XLSX_ARCHIVE_BYTES,
                    self::MAX_XLSX_TOTAL_BYTES,
                    $dataRowFilter,
                );
        } catch (InvalidThematicStatisticWorkbook $exception) {
            throw $exception;
        } catch (InvalidPopulationStatisticWorkbook $exception) {
            throw new InvalidThematicStatisticWorkbook($exception->getMessage(), previous: $exception);
        } catch (Throwable $exception) {
            throw new InvalidThematicStatisticWorkbook("Workbook {$filename} tidak dapat dibaca.", previous: $exception);
        }

        $parsed = match (true) {
            $type === ThematicStatisticType::EducationBySingleAge => $this->parseEducationBySingleAge($rows, $filename),
            $type->isBirthCertificate() => $this->parseBirthCertificate($rows, $filename),
            $type === ThematicStatisticType::ChildIdentityCard => $this->parseChildIdentityCard($rows, $filename),
            $type === ThematicStatisticType::DeathCertificate => $this->parseDeathCertificate($rows, $filename),
            default => $this->parseLaborForceParticipation($rows, $type, $filename),
        };
        $fileHash = hash_file('sha256', $path);

        if (! is_string($fileHash)) {
            throw new InvalidThematicStatisticWorkbook("Checksum workbook {$filename} tidak dapat dihitung.");
        }

        return [
            'type' => $type->value,
            'title' => $type->title(),
            'filename' => $filename,
            'file_hash' => $fileHash,
            ...$parsed,
        ];
    }

    /** @return callable(array<string, string|null>, array<string, string|null>): bool */
    private function regencyRowFilter(): callable
    {
        $codeColumn = null;

        return function (array $row, array $header) use (&$codeColumn): bool {
            if ($codeColumn === null) {
                foreach ($header as $column => $value) {
                    if ($this->canonicalHeader((string) $value) === 'KODE') {
                        $codeColumn = $column;
                        break;
                    }
                }
            }

            if ($codeColumn === null) {
                return true;
            }

            return $this->areaCodeNormalizer->normalize((string) ($row[$codeColumn] ?? '')) === '75.01';
        };
    }

    /**
     * @param  list<array<string, string|null>>  $rows
     * @return array{row_count: int, area_count: int, observation_count: int, warnings: list<string>, observations: list<array<string, string|null>>}
     */
    private function parseLaborForceParticipation(array $rows, ThematicStatisticType $type, string $filename): array
    {
        [$header, $dataRows] = $this->splitRows($rows, $filename);
        $columns = $this->columnsByHeader($header);
        $codeColumn = $this->requiredColumn($columns, 'KODE', $filename);
        $areaColumn = $this->requiredColumn($columns, 'WILAYAH', $filename);
        [$dimensionName, $dimensionColumn] = match ($type) {
            ThematicStatisticType::LaborForceParticipationByAge => ['age_group', $this->requiredColumn($columns, 'KELUMUR', $filename)],
            ThematicStatisticType::LaborForceParticipationByEducation => ['education', $this->requiredColumn($columns, 'PENDIDIKAN', $filename)],
            ThematicStatisticType::LaborForceParticipationByFamilyRelationship => ['family_relationship', $this->requiredColumn($columns, 'STATUSHUBKELUARGA', $filename)],
            default => ['', null],
        };
        $metricHeaders = [
            'not_working' => 'TDKBEKERJA',
            'employed' => 'BEKERJA',
            'labor_force' => 'AK',
            'non_labor_force' => 'BAK',
        ];
        $sexHeaders = ['male' => 'L', 'female' => 'P', 'total' => 'JML'];
        $metricColumns = [];

        foreach ($metricHeaders as $metric => $prefix) {
            foreach ($sexHeaders as $sex => $suffix) {
                $metricColumns[$metric][$sex] = $this->requiredColumn($columns, $prefix.$suffix, $filename);
            }
        }

        foreach ($sexHeaders as $sex => $suffix) {
            $metricColumns['population'][$sex] = $this->requiredColumn($columns, $suffix, $filename);
            $metricColumns['participation_rate'][$sex] = $this->requiredColumn($columns, 'APAK'.$suffix, $filename);
        }

        $observations = [];
        $warnings = [];
        $areas = [];
        $rowCount = 0;

        foreach ($dataRows as $index => $row) {
            $rowNumber = $index + 2;
            $areaIdentity = $this->areaIdentity($row, $codeColumn, $areaColumn, $filename, $rowNumber, $warnings);

            if ($areaIdentity === null || $areaIdentity['area_level'] === AdministrativeAreaLevel::Village->value) {
                continue;
            }

            $dimensionValue = $dimensionColumn === null ? '' : Str::squish((string) ($row[$dimensionColumn] ?? ''));

            if ($dimensionColumn !== null && $dimensionValue === '') {
                throw new InvalidThematicStatisticWorkbook("Dimensi data kosong pada baris {$rowNumber} di workbook {$filename}.");
            }

            $counts = [];

            foreach (array_keys($metricHeaders) as $metric) {
                foreach (array_keys($sexHeaders) as $sex) {
                    $counts[$metric][$sex] = $this->nonNegativeInteger(
                        $row[$metricColumns[$metric][$sex]] ?? null,
                        $filename,
                        $rowNumber,
                    );
                }

                $this->assertSexTotal($counts[$metric], $metric, $filename, $rowNumber);
            }

            foreach (array_keys($sexHeaders) as $sex) {
                $counts['population'][$sex] = $this->nonNegativeInteger(
                    $row[$metricColumns['population'][$sex]] ?? null,
                    $filename,
                    $rowNumber,
                );
            }

            $this->assertSexTotal($counts['population'], 'population', $filename, $rowNumber);

            foreach (array_keys($sexHeaders) as $sex) {
                if ($counts['not_working'][$sex] + $counts['employed'][$sex] !== $counts['labor_force'][$sex]
                    || $counts['labor_force'][$sex] + $counts['non_labor_force'][$sex] !== $counts['population'][$sex]) {
                    throw new InvalidThematicStatisticWorkbook("Komponen APAK tidak konsisten pada baris {$rowNumber} di workbook {$filename}.");
                }

                $sourceRate = $this->nonNegativeDecimal(
                    $row[$metricColumns['participation_rate'][$sex]] ?? null,
                    $filename,
                    $rowNumber,
                );
                $calculatedRate = $this->percentage($counts['labor_force'][$sex], $counts['population'][$sex]);
                $this->assertPercentageMatches($sourceRate, $calculatedRate, $filename, $rowNumber);
            }

            foreach (['not_working', 'employed', 'labor_force', 'non_labor_force', 'population'] as $metric) {
                foreach (array_keys($sexHeaders) as $sex) {
                    $observations[] = $this->observation(
                        $areaIdentity,
                        $metric,
                        $sex,
                        (string) $counts[$metric][$sex],
                        'people',
                        $dimensionName,
                        $dimensionValue,
                    );
                }
            }

            foreach (array_keys($sexHeaders) as $sex) {
                $observations[] = $this->observation(
                    $areaIdentity,
                    'participation_rate',
                    $sex,
                    number_format($this->percentage($counts['labor_force'][$sex], $counts['population'][$sex]), 4, '.', ''),
                    'percent',
                    $dimensionName,
                    $dimensionValue,
                );
            }

            $areas[$areaIdentity['area_code']] = true;
            $rowCount++;
        }

        return $this->result($observations, $warnings, $areas, $rowCount, $filename);
    }

    /**
     * @param  list<array<string, string|null>>  $rows
     * @return array{row_count: int, area_count: int, observation_count: int, warnings: list<string>, observations: list<array<string, string|null>>}
     */
    private function parseEducationBySingleAge(array $rows, string $filename): array
    {
        [$header, $dataRows] = $this->splitRows($rows, $filename);
        $columns = $this->columnsByHeader($header);
        $codeColumn = $this->requiredColumn($columns, 'KODE', $filename);
        $areaColumn = $this->requiredColumn($columns, 'WILAYAH', $filename);
        $ageColumn = $this->requiredColumn($columns, 'UMUR', $filename);
        $educationHeaders = [
            'Tidak/belum sekolah' => 'TIDAKBLMSEKOLAH',
            'Belum tamat SD/sederajat' => 'BELUMTAMATSDSEDERAJAT',
            'Tamat SD/sederajat' => 'TAMATSDSEDERAJAT',
            'SLTP/sederajat' => 'SLTPSEDERAJAT',
            'SLTA/sederajat' => 'SLTASEDERAJAT',
            'Diploma I/II' => 'DIPLOMAIII',
            'Akademi/Diploma III/Sarjana Muda' => 'AKADEMIDIPLOMAIIISMUDA',
            'Diploma IV/Strata I' => 'DIPLOMAIVSTRATAI',
            'Strata II' => 'STRATAII',
            'Strata III' => 'STRATAIII',
        ];
        $suffixes = ['male' => 'LK', 'female' => 'PR', 'total' => 'JML'];
        $educationColumns = [];

        foreach ($educationHeaders as $education => $prefix) {
            foreach ($suffixes as $sex => $suffix) {
                $educationColumns[$education][$sex] = $this->requiredColumn($columns, $prefix.$suffix, $filename);
            }
        }

        $observations = [];
        $warnings = [];
        $areas = [];
        $rowCount = 0;

        foreach ($dataRows as $index => $row) {
            $rowNumber = $index + 2;
            $areaIdentity = $this->areaIdentity($row, $codeColumn, $areaColumn, $filename, $rowNumber, $warnings);

            if ($areaIdentity === null || $areaIdentity['area_level'] !== AdministrativeAreaLevel::Regency->value) {
                continue;
            }

            $age = $this->nonNegativeInteger($row[$ageColumn] ?? null, $filename, $rowNumber);

            if ($age > 130) {
                throw new InvalidThematicStatisticWorkbook("Umur pada baris {$rowNumber} di workbook {$filename} melebihi 130 tahun.");
            }

            foreach ($educationColumns as $education => $sexColumns) {
                $values = [];

                foreach ($sexColumns as $sex => $column) {
                    $values[$sex] = $this->nonNegativeInteger($row[$column] ?? null, $filename, $rowNumber);
                }

                $this->assertSexTotal($values, $education, $filename, $rowNumber);

                foreach ($values as $sex => $value) {
                    $observations[] = $this->observation(
                        $areaIdentity,
                        'population',
                        $sex,
                        (string) $value,
                        'people',
                        'single_age',
                        (string) $age,
                        'education',
                        $education,
                    );
                }
            }

            $areas[$areaIdentity['area_code']] = true;
            $rowCount++;
        }

        return $this->result($observations, $warnings, $areas, $rowCount, $filename);
    }

    /**
     * @param  list<array<string, string|null>>  $rows
     * @return array{row_count: int, area_count: int, observation_count: int, warnings: list<string>, observations: list<array<string, string|null>>}
     */
    private function parseBirthCertificate(array $rows, string $filename): array
    {
        return $this->parseDocumentCoverage(
            $rows,
            $filename,
            ['male' => 'WAJIBAKTADINAMISLK', 'female' => 'WAJIBAKTADINAMISPR', 'total' => 'WAJIBAKTADINAMISJML'],
            ['male' => 'MEMILIKIDINAMISLK', 'female' => 'MEMILIKIDINAMISPR', 'total' => 'MEMILIKIDINAMISJML'],
            ['male' => 'BELUMMEMILIKIDINAMISLK', 'female' => 'BELUMMEMILIKIDINAMISPR', 'total' => 'BELUMMEMILIKIDINAMISJML'],
            'PERSENDINAMIS',
        );
    }

    /**
     * @param  list<array<string, string|null>>  $rows
     * @return array{row_count: int, area_count: int, observation_count: int, warnings: list<string>, observations: list<array<string, string|null>>}
     */
    private function parseChildIdentityCard(array $rows, string $filename): array
    {
        return $this->parseDocumentCoverage(
            $rows,
            $filename,
            ['male' => 'JUMLAHDINAMISLK', 'female' => 'JUMLAHDINAMISPR', 'total' => 'JUMLAHDINAMISTTL'],
            ['male' => 'MEMILIKIDINAMISLK', 'female' => 'MEMILIKIDINAMISPR', 'total' => 'MEMILIKIDINAMISJML'],
            ['male' => 'BELUMMEMILIKIDINAMISLK', 'female' => 'BELUMMEMILIKIDINAMISPR', 'total' => 'BELUMMEMILIKIDINAMISJML'],
            'PERSENDINAMIS',
        );
    }

    /**
     * @param  list<array<string, string|null>>  $rows
     * @param  array{male: string, female: string, total: string}  $targetHeaders
     * @param  array{male: string, female: string, total: string}  $ownedHeaders
     * @param  array{male: string, female: string, total: string}  $notOwnedHeaders
     * @return array{row_count: int, area_count: int, observation_count: int, warnings: list<string>, observations: list<array<string, string|null>>}
     */
    private function parseDocumentCoverage(
        array $rows,
        string $filename,
        array $targetHeaders,
        array $ownedHeaders,
        array $notOwnedHeaders,
        string $sourcePercentageHeader,
    ): array {
        [$header, $dataRows] = $this->splitRows($rows, $filename);
        $columns = $this->columnsByHeader($header);
        $codeColumn = $this->requiredColumn($columns, 'KODE', $filename);
        $areaColumn = $this->requiredColumn($columns, 'WILAYAH', $filename);
        $percentageColumn = $this->requiredColumn($columns, $sourcePercentageHeader, $filename);
        $metricHeaders = ['target' => $targetHeaders, 'owned' => $ownedHeaders, 'not_owned' => $notOwnedHeaders];
        $metricColumns = [];

        foreach ($metricHeaders as $metric => $headers) {
            foreach ($headers as $sex => $headerName) {
                $metricColumns[$metric][$sex] = $this->requiredColumn($columns, $headerName, $filename);
            }
        }

        $observations = [];
        $warnings = [];
        $areas = [];
        $rowCount = 0;

        foreach ($dataRows as $index => $row) {
            $rowNumber = $index + 2;
            $areaIdentity = $this->areaIdentity($row, $codeColumn, $areaColumn, $filename, $rowNumber, $warnings);

            if ($areaIdentity === null || $areaIdentity['area_level'] === AdministrativeAreaLevel::Village->value) {
                continue;
            }

            $values = [];

            foreach ($metricColumns as $metric => $sexColumns) {
                foreach ($sexColumns as $sex => $column) {
                    $values[$metric][$sex] = $this->nonNegativeInteger($row[$column] ?? null, $filename, $rowNumber);
                }

                $this->assertSexTotal($values[$metric], $metric, $filename, $rowNumber);
            }

            foreach (['male', 'female', 'total'] as $sex) {
                if ($values['owned'][$sex] + $values['not_owned'][$sex] !== $values['target'][$sex]) {
                    throw new InvalidThematicStatisticWorkbook("Jumlah memiliki dan belum memiliki tidak sama dengan sasaran pada baris {$rowNumber} di workbook {$filename}.");
                }
            }

            $sourcePercentage = $this->nonNegativeDecimal($row[$percentageColumn] ?? null, $filename, $rowNumber);
            $calculatedTotalPercentage = $this->percentage($values['owned']['total'], $values['target']['total']);
            $this->assertPercentageMatches($sourcePercentage, $calculatedTotalPercentage, $filename, $rowNumber);

            foreach ($values as $metric => $sexValues) {
                foreach ($sexValues as $sex => $value) {
                    $observations[] = $this->observation($areaIdentity, $metric, $sex, (string) $value, 'people');
                }
            }

            foreach (['male', 'female', 'total'] as $sex) {
                $observations[] = $this->observation(
                    $areaIdentity,
                    'coverage',
                    $sex,
                    number_format($this->percentage($values['owned'][$sex], $values['target'][$sex]), 4, '.', ''),
                    'percent',
                );
            }

            $areas[$areaIdentity['area_code']] = true;
            $rowCount++;
        }

        return $this->result($observations, $warnings, $areas, $rowCount, $filename);
    }

    /**
     * @param  list<array<string, string|null>>  $rows
     * @return array{row_count: int, area_count: int, observation_count: int, warnings: list<string>, observations: list<array<string, string|null>>}
     */
    private function parseDeathCertificate(array $rows, string $filename): array
    {
        [$header, $dataRows] = $this->splitRows($rows, $filename);
        $columns = $this->columnsByHeader($header);
        $codeColumn = $this->requiredColumn($columns, 'KODE', $filename);
        $areaColumn = $this->requiredColumn($columns, 'WILAYAH', $filename);
        $headers = [
            'issued' => ['male' => 'TERBITAKTALK', 'female' => 'TERBITAKTAPR', 'total' => 'TERBITAKTAJML'],
            'cancelled' => ['male' => 'BATALAKTALK', 'female' => 'BATALAKTAPR', 'total' => 'BATALAKTAJML'],
            'deaths' => ['male' => 'MENINGGALLK', 'female' => 'MENINGGALPR', 'total' => 'MENINGGALJML'],
        ];
        $observations = [];
        $warnings = [];
        $areas = [];
        $rowCount = 0;

        foreach ($dataRows as $index => $row) {
            $rowNumber = $index + 2;
            $areaIdentity = $this->areaIdentity($row, $codeColumn, $areaColumn, $filename, $rowNumber, $warnings);

            if ($areaIdentity === null || $areaIdentity['area_level'] === AdministrativeAreaLevel::Village->value) {
                continue;
            }

            $values = [];

            foreach ($headers as $metric => $sexHeaders) {
                foreach ($sexHeaders as $sex => $headerName) {
                    $column = $this->requiredColumn($columns, $headerName, $filename);
                    $values[$metric][$sex] = $this->nonNegativeInteger($row[$column] ?? null, $filename, $rowNumber);
                }

                $this->assertSexTotal($values[$metric], $metric, $filename, $rowNumber);
            }

            foreach (['male', 'female', 'total'] as $sex) {
                if ($values['issued'][$sex] - $values['cancelled'][$sex] !== $values['deaths'][$sex]) {
                    throw new InvalidThematicStatisticWorkbook("Jumlah Akta Kematian tidak konsisten pada baris {$rowNumber} di workbook {$filename}.");
                }
            }

            foreach ($values as $metric => $sexValues) {
                foreach ($sexValues as $sex => $value) {
                    $observations[] = $this->observation($areaIdentity, $metric, $sex, (string) $value, 'records');
                }
            }

            $areas[$areaIdentity['area_code']] = true;
            $rowCount++;
        }

        return $this->result($observations, $warnings, $areas, $rowCount, $filename);
    }

    /**
     * @param  list<array<string, string|null>>  $rows
     * @return array{0: array<string, string|null>, 1: list<array<string, string|null>>}
     */
    private function splitRows(array $rows, string $filename): array
    {
        $header = array_shift($rows);

        if (! is_array($header)) {
            throw new InvalidThematicStatisticWorkbook("Workbook {$filename} tidak memiliki header.");
        }

        return [$header, $rows];
    }

    /** @param array<string, string|null> $header */
    private function columnsByHeader(array $header): array
    {
        $columns = [];

        foreach ($header as $column => $value) {
            $canonical = $this->canonicalHeader((string) $value);

            if ($canonical !== '') {
                $columns[$canonical] = $column;
            }
        }

        return $columns;
    }

    /** @param array<string, string> $columns */
    private function requiredColumn(array $columns, string $header, string $filename): string
    {
        return $columns[$this->canonicalHeader($header)]
            ?? throw new InvalidThematicStatisticWorkbook("Kolom {$header} tidak ditemukan pada workbook {$filename}.");
    }

    private function canonicalHeader(string $header): string
    {
        return preg_replace('/[^A-Z0-9]+/', '', Str::upper($header)) ?? '';
    }

    /**
     * @param  array<string, string|null>  $row
     * @param  list<string>  $warnings
     * @return array{area_code: string, area: string, area_level: string, parent_area_code: string|null}|null
     */
    private function areaIdentity(
        array $row,
        string $codeColumn,
        string $areaColumn,
        string $filename,
        int $rowNumber,
        array &$warnings,
    ): ?array {
        $areaCode = $this->areaCodeNormalizer->normalize((string) ($row[$codeColumn] ?? ''));
        $area = Str::squish((string) ($row[$areaColumn] ?? ''));

        if ($areaCode === '' && $area === '') {
            return null;
        }

        if ($area === '') {
            $warnings[] = "Baris {$rowNumber} dilewati karena nama wilayah kosong.";

            return null;
        }

        $areaLevel = match (true) {
            preg_match('/^75\.01$/', $areaCode) === 1 => AdministrativeAreaLevel::Regency,
            preg_match('/^75\.01\.\d{2}$/', $areaCode) === 1 => AdministrativeAreaLevel::District,
            preg_match('/^75\.01\.\d{2}\.\d{4}$/', $areaCode) === 1 => AdministrativeAreaLevel::Village,
            default => throw new InvalidThematicStatisticWorkbook("Kode wilayah {$areaCode} pada baris {$rowNumber} di workbook {$filename} tidak valid."),
        };

        return [
            'area_code' => $areaCode,
            'area' => $area,
            'area_level' => $areaLevel->value,
            'parent_area_code' => match ($areaLevel) {
                AdministrativeAreaLevel::Regency => null,
                AdministrativeAreaLevel::District => '75.01',
                AdministrativeAreaLevel::Village => Str::beforeLast($areaCode, '.'),
                AdministrativeAreaLevel::Legacy => null,
            },
        ];
    }

    private function nonNegativeInteger(?string $value, string $filename, int $rowNumber): int
    {
        $value = trim((string) $value);

        if (preg_match('/^\d+$/', $value) !== 1 || strlen($value) > 10 || (int) $value > 1_000_000_000) {
            throw new InvalidThematicStatisticWorkbook("Nilai pada baris {$rowNumber} di workbook {$filename} harus bilangan bulat nonnegatif.");
        }

        return (int) $value;
    }

    private function nonNegativeDecimal(?string $value, string $filename, int $rowNumber): float
    {
        $value = trim((string) $value);

        if (preg_match('/^\d+(?:\.\d{1,4})?$/', $value) !== 1 || (float) $value > 100) {
            throw new InvalidThematicStatisticWorkbook("Persentase pada baris {$rowNumber} di workbook {$filename} tidak valid.");
        }

        return (float) $value;
    }

    /** @param array{male: int, female: int, total: int} $values */
    private function assertSexTotal(array $values, string $metric, string $filename, int $rowNumber): void
    {
        if ($values['total'] !== $values['male'] + $values['female']) {
            throw new InvalidThematicStatisticWorkbook("Nilai laki-laki dan perempuan untuk {$metric} tidak sama dengan jumlah pada baris {$rowNumber} di workbook {$filename}.");
        }
    }

    private function percentage(int $numerator, int $denominator): float
    {
        return $denominator === 0 ? 0.0 : round(($numerator / $denominator) * 100, 4);
    }

    private function assertPercentageMatches(float $source, float $calculated, string $filename, int $rowNumber): void
    {
        if (abs($source - round($calculated, 2)) > 0.011) {
            throw new InvalidThematicStatisticWorkbook("Persentase pada baris {$rowNumber} di workbook {$filename} tidak cocok dengan pembilang dan penyebut.");
        }
    }

    /**
     * @param  array{area_code: string, area: string, area_level: string, parent_area_code: string|null}  $area
     * @return array<string, string|null>
     */
    private function observation(
        array $area,
        string $metric,
        string $sex,
        string $value,
        string $unit,
        string $dimensionOne = '',
        string $dimensionOneValue = '',
        string $dimensionTwo = '',
        string $dimensionTwoValue = '',
    ): array {
        $identity = implode('|', [
            $area['area_code'],
            $dimensionOne,
            $dimensionOneValue,
            $dimensionTwo,
            $dimensionTwoValue,
            $metric,
            $sex,
            'dynamic',
        ]);

        return [
            ...$area,
            'dimension_one' => $dimensionOne,
            'dimension_one_value' => $dimensionOneValue,
            'dimension_two' => $dimensionTwo,
            'dimension_two_value' => $dimensionTwoValue,
            'metric' => $metric,
            'sex' => $sex,
            'snapshot' => 'dynamic',
            'value' => $value,
            'unit' => $unit,
            'observation_key' => sha1($identity),
        ];
    }

    /**
     * @param  list<array<string, string|null>>  $observations
     * @param  list<string>  $warnings
     * @param  array<string, bool>  $areas
     * @return array{row_count: int, area_count: int, observation_count: int, warnings: list<string>, observations: list<array<string, string|null>>}
     */
    private function result(array $observations, array $warnings, array $areas, int $rowCount, string $filename): array
    {
        if ($rowCount === 0 || $observations === []) {
            throw new InvalidThematicStatisticWorkbook("Workbook {$filename} tidak menghasilkan data publik yang valid.");
        }

        if (count(array_unique(array_column($observations, 'observation_key'))) !== count($observations)) {
            throw new InvalidThematicStatisticWorkbook("Workbook {$filename} menghasilkan observasi ganda.");
        }

        return [
            'row_count' => $rowCount,
            'area_count' => count($areas),
            'observation_count' => count($observations),
            'warnings' => array_values(array_unique($warnings)),
            'observations' => $observations,
        ];
    }
}
