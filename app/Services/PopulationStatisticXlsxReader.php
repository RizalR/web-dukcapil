<?php

namespace App\Services;

use App\AdministrativeAreaLevel;
use App\Exceptions\InvalidPopulationStatisticWorkbook;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class PopulationStatisticXlsxReader
{
    private const int MAX_ARCHIVE_BYTES = 2_097_152;

    private const int MAX_ARCHIVE_ENTRIES = 100;

    private const int MAX_ENTRY_BYTES = 5_242_880;

    private const int MAX_POPULATION_COUNT = 1_000_000_000;

    private const int MAX_ROWS = 1_000;

    private const int MAX_TOTAL_UNCOMPRESSED_BYTES = 20_971_520;

    /**
     * @return array{
     *     rows: list<array{area_code: string, area: string, area_level: string, parent_area_code: string|null, male: int, female: int, total: int}>,
     *     summary: array{regency_count: int, district_count: int, village_count: int, male: int, female: int, total: int}
     * }
     */
    public function read(UploadedFile $file): array
    {
        $path = $file->getRealPath();

        if ($path === false) {
            throw new InvalidPopulationStatisticWorkbook('File Excel tidak dapat dibaca atau melebihi 2 MB.');
        }

        return $this->validateRows($this->readTabularFile($path, 'Sheet1'));
    }

    /** @return list<array<string, string|null>> */
    public function readTabularFile(string $path, string $sheetName): array
    {
        $size = @filesize($path);

        if (! is_file($path) || ! is_readable($path) || $size === false || $size > self::MAX_ARCHIVE_BYTES) {
            throw new InvalidPopulationStatisticWorkbook('File Excel tidak dapat dibaca atau melebihi 2 MB.');
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new InvalidPopulationStatisticWorkbook('File Excel tidak dapat dibaca.');
        }

        $entries = $this->readArchiveDirectory($contents);
        $worksheetPath = $this->findWorksheetPath($contents, $entries, $sheetName);
        $sharedStrings = $this->readSharedStrings($contents, $entries);

        return $this->readWorksheetRows($this->extractEntry($contents, $entries, $worksheetPath), $sharedStrings);
    }

    /**
     * @return array<string, array{compression: int, compressed_size: int, uncompressed_size: int, crc: int, flags: int, local_offset: int}>
     */
    private function readArchiveDirectory(string $contents): array
    {
        $searchStart = max(0, strlen($contents) - 65_557);
        $endRecordPosition = strrpos(substr($contents, $searchStart), "PK\x05\x06");

        if ($endRecordPosition === false) {
            throw new InvalidPopulationStatisticWorkbook('File bukan arsip XLSX yang valid.');
        }

        $endRecordOffset = $searchStart + $endRecordPosition;

        if ($endRecordOffset + 22 > strlen($contents)) {
            throw new InvalidPopulationStatisticWorkbook('Catatan akhir arsip XLSX terpotong.');
        }

        $record = unpack(
            'vdisk/vcentral_disk/ventries_disk/ventries/Vcentral_size/Vcentral_offset/vcomment_length',
            substr($contents, $endRecordOffset + 4, 18),
        );

        if (! is_array($record)
            || $record['disk'] !== 0
            || $record['central_disk'] !== 0
            || $record['entries'] !== $record['entries_disk']
            || $record['entries'] < 1
            || $record['entries'] > self::MAX_ARCHIVE_ENTRIES
            || $endRecordOffset + 22 + $record['comment_length'] > strlen($contents)
            || $endRecordOffset < $record['central_offset'] + $record['central_size']) {
            throw new InvalidPopulationStatisticWorkbook('Struktur arsip XLSX tidak didukung.');
        }

        $entries = [];
        $offset = $record['central_offset'];
        $totalUncompressedBytes = 0;

        for ($entryNumber = 0; $entryNumber < $record['entries']; $entryNumber++) {
            if ($offset + 46 > strlen($contents) || substr($contents, $offset, 4) !== "PK\x01\x02") {
                throw new InvalidPopulationStatisticWorkbook('Direktori arsip XLSX rusak.');
            }

            $header = unpack(
                'vversion_made/vversion_needed/vflags/vcompression/vmod_time/vmod_date/Vcrc/Vcompressed_size/Vuncompressed_size/vfilename_length/vextra_length/vcomment_length/vdisk_start/vinternal_attributes/Vexternal_attributes/Vlocal_offset',
                substr($contents, $offset + 4, 42),
            );

            if (! is_array($header) || $header['filename_length'] < 1 || $header['filename_length'] > 512) {
                throw new InvalidPopulationStatisticWorkbook('Nama berkas internal XLSX tidak valid.');
            }

            $nextOffset = $offset + 46 + $header['filename_length'] + $header['extra_length'] + $header['comment_length'];

            if ($nextOffset > $record['central_offset'] + $record['central_size'] || $nextOffset > strlen($contents)) {
                throw new InvalidPopulationStatisticWorkbook('Direktori arsip XLSX terpotong.');
            }

            $filename = str_replace('\\', '/', substr($contents, $offset + 46, $header['filename_length']));
            $this->assertSafeArchivePath($filename);

            if (isset($entries[$filename])) {
                throw new InvalidPopulationStatisticWorkbook('Arsip XLSX memuat nama berkas internal ganda.');
            }

            if (($header['flags'] & 1) === 1 || ! in_array($header['compression'], [0, 8], true)) {
                throw new InvalidPopulationStatisticWorkbook('Arsip XLSX terenkripsi atau menggunakan kompresi yang tidak didukung.');
            }

            if ($header['uncompressed_size'] > self::MAX_ENTRY_BYTES
                || ($header['compressed_size'] > 0
                    && $header['uncompressed_size'] > 1_048_576
                    && $header['uncompressed_size'] / $header['compressed_size'] > 100)) {
                throw new InvalidPopulationStatisticWorkbook('Ukuran berkas internal XLSX tidak aman.');
            }

            $lowerFilename = Str::lower($filename);

            if (str_contains($lowerFilename, 'vbaproject') || str_contains($lowerFilename, 'externallinks/')) {
                throw new InvalidPopulationStatisticWorkbook('Workbook dengan makro atau tautan eksternal tidak diizinkan.');
            }

            $totalUncompressedBytes += $header['uncompressed_size'];

            if ($totalUncompressedBytes > self::MAX_TOTAL_UNCOMPRESSED_BYTES) {
                throw new InvalidPopulationStatisticWorkbook('Total ukuran isi XLSX melebihi batas aman.');
            }

            $entries[$filename] = [
                'compression' => $header['compression'],
                'compressed_size' => $header['compressed_size'],
                'uncompressed_size' => $header['uncompressed_size'],
                'crc' => $header['crc'],
                'flags' => $header['flags'],
                'local_offset' => $header['local_offset'],
            ];

            $offset = $nextOffset;
        }

        return $entries;
    }

    /**
     * @param  array<string, array{compression: int, compressed_size: int, uncompressed_size: int, crc: int, flags: int, local_offset: int}>  $entries
     */
    private function extractEntry(string $contents, array $entries, string $filename): string
    {
        $entry = $entries[$filename] ?? null;

        if ($entry === null
            || strlen($contents) < $entry['local_offset'] + 30
            || substr($contents, $entry['local_offset'], 4) !== "PK\x03\x04") {
            throw new InvalidPopulationStatisticWorkbook("Berkas internal {$filename} tidak ditemukan.");
        }

        $localHeader = unpack(
            'vversion_needed/vflags/vcompression/vmod_time/vmod_date/Vcrc/Vcompressed_size/Vuncompressed_size/vfilename_length/vextra_length',
            substr($contents, $entry['local_offset'] + 4, 26),
        );

        if (! is_array($localHeader)
            || $localHeader['compression'] !== $entry['compression']
            || ($localHeader['flags'] & 1) === 1) {
            throw new InvalidPopulationStatisticWorkbook('Header berkas internal XLSX tidak konsisten.');
        }

        $localFilename = str_replace('\\', '/', substr(
            $contents,
            $entry['local_offset'] + 30,
            $localHeader['filename_length'],
        ));

        if ($localFilename !== $filename) {
            throw new InvalidPopulationStatisticWorkbook('Nama berkas internal XLSX tidak konsisten.');
        }

        $dataOffset = $entry['local_offset'] + 30 + $localHeader['filename_length'] + $localHeader['extra_length'];

        if ($dataOffset + $entry['compressed_size'] > strlen($contents)) {
            throw new InvalidPopulationStatisticWorkbook('Isi arsip XLSX terpotong.');
        }

        $compressed = substr($contents, $dataOffset, $entry['compressed_size']);

        if (strlen($compressed) !== $entry['compressed_size']) {
            throw new InvalidPopulationStatisticWorkbook('Isi arsip XLSX terpotong.');
        }

        $uncompressed = match ($entry['compression']) {
            0 => $compressed,
            8 => @gzinflate($compressed),
        };

        if (! is_string($uncompressed)
            || strlen($uncompressed) !== $entry['uncompressed_size']
            || (string) sprintf('%u', crc32($uncompressed)) !== (string) $entry['crc']) {
            throw new InvalidPopulationStatisticWorkbook('Isi berkas internal XLSX rusak.');
        }

        return $uncompressed;
    }

    /**
     * @param  array<string, array{compression: int, compressed_size: int, uncompressed_size: int, crc: int, flags: int, local_offset: int}>  $entries
     */
    private function findWorksheetPath(string $contents, array $entries, string $sheetName): string
    {
        $workbook = $this->xml($this->extractEntry($contents, $entries, 'xl/workbook.xml'));
        $relationships = $this->xml($this->extractEntry($contents, $entries, 'xl/_rels/workbook.xml.rels'));
        $workbookXpath = new DOMXPath($workbook);
        $relationshipXpath = new DOMXPath($relationships);
        $relationshipId = null;

        foreach ($workbookXpath->query('//*[local-name()="sheet"]') ?: [] as $sheet) {
            if ($sheet instanceof DOMElement && strcasecmp($sheet->getAttribute('name'), $sheetName) === 0) {
                $relationshipId = $sheet->getAttributeNS(
                    'http://schemas.openxmlformats.org/officeDocument/2006/relationships',
                    'id',
                );
                break;
            }
        }

        if ($relationshipId === null || $relationshipId === '') {
            throw new InvalidPopulationStatisticWorkbook("Sheet {$sheetName} tidak ditemukan.");
        }

        foreach ($relationshipXpath->query('//*[local-name()="Relationship"]') ?: [] as $relationship) {
            if (! $relationship instanceof DOMElement || $relationship->getAttribute('Id') !== $relationshipId) {
                continue;
            }

            if (strcasecmp($relationship->getAttribute('TargetMode'), 'External') === 0) {
                throw new InvalidPopulationStatisticWorkbook('Worksheet eksternal tidak diizinkan.');
            }

            return $this->normalizeWorksheetPath($relationship->getAttribute('Target'));
        }

        throw new InvalidPopulationStatisticWorkbook("Relasi sheet {$sheetName} tidak ditemukan.");
    }

    /**
     * @param  array<string, array{compression: int, compressed_size: int, uncompressed_size: int, crc: int, flags: int, local_offset: int}>  $entries
     * @return list<string>
     */
    private function readSharedStrings(string $contents, array $entries): array
    {
        if (! isset($entries['xl/sharedStrings.xml'])) {
            return [];
        }

        $document = $this->xml($this->extractEntry($contents, $entries, 'xl/sharedStrings.xml'));
        $xpath = new DOMXPath($document);
        $strings = [];

        foreach ($xpath->query('//*[local-name()="si"]') ?: [] as $item) {
            $strings[] = $item->textContent;
        }

        return $strings;
    }

    /**
     * @param  list<string>  $sharedStrings
     * @return list<array<string, string|null>>
     */
    private function readWorksheetRows(string $worksheetXml, array $sharedStrings): array
    {
        $worksheet = $this->xml($worksheetXml);
        $xpath = new DOMXPath($worksheet);
        $rows = [];

        foreach ($xpath->query('//*[local-name()="sheetData"]/*[local-name()="row"]') ?: [] as $row) {
            if (count($rows) >= self::MAX_ROWS + 1) {
                throw new InvalidPopulationStatisticWorkbook('Workbook memuat lebih dari 1.000 baris data.');
            }

            $values = [];

            foreach ($xpath->query('./*[local-name()="c"]', $row) ?: [] as $cell) {
                if (! $cell instanceof DOMElement) {
                    continue;
                }

                $reference = strtoupper($cell->getAttribute('r'));

                if (preg_match('/^([A-Z]+)\d+$/', $reference, $matches) !== 1) {
                    continue;
                }

                $values[$matches[1]] = $this->cellValue($xpath, $cell, $sharedStrings);
            }

            $rows[] = $values;
        }

        return $rows;
    }

    /**
     * @param  list<string>  $sharedStrings
     */
    private function cellValue(DOMXPath $xpath, DOMElement $cell, array $sharedStrings): ?string
    {
        if ($cell->getAttribute('t') === 'inlineStr') {
            $inlineString = $xpath->query('.//*[local-name()="t"]', $cell);
            $value = '';

            if ($inlineString === false) {
                return null;
            }

            foreach ($inlineString as $text) {
                $value .= $text->textContent;
            }

            return $value;
        }

        $value = $xpath->query('./*[local-name()="v"]', $cell)?->item(0)?->textContent;

        if ($value === null) {
            return null;
        }

        if ($cell->getAttribute('t') !== 's') {
            return $value;
        }

        if (preg_match('/^\d+$/', $value) !== 1 || ! array_key_exists((int) $value, $sharedStrings)) {
            throw new InvalidPopulationStatisticWorkbook('Referensi teks pada workbook tidak valid.');
        }

        return $sharedStrings[(int) $value];
    }

    /**
     * @param  list<array<string, string|null>>  $rows
     * @return array{
     *     rows: list<array{area_code: string, area: string, area_level: string, parent_area_code: string|null, male: int, female: int, total: int}>,
     *     summary: array{regency_count: int, district_count: int, village_count: int, male: int, female: int, total: int}
     * }
     */
    private function validateRows(array $rows): array
    {
        $headers = array_map(
            fn (string $column): string => Str::upper(trim((string) ($rows[0][$column] ?? ''))),
            ['A', 'B', 'C', 'D', 'E'],
        );

        if ($headers !== ['KODE', 'WILAYAH', 'L', 'P', 'JML']) {
            throw new InvalidPopulationStatisticWorkbook('Header Sheet1 harus KODE, WILAYAH, L, P, dan JML.');
        }

        $normalizedRows = [];
        $codes = [];

        foreach (array_slice($rows, 1) as $index => $row) {
            $rowNumber = $index + 2;

            if (collect($row)->filter(fn (?string $value): bool => $value !== null && trim($value) !== '')->isEmpty()) {
                continue;
            }

            $areaCode = trim((string) ($row['A'] ?? ''));
            $area = Str::squish((string) ($row['B'] ?? ''));

            if (preg_match('/^75\.01(?:\.\d{2}(?:\.\d{4})?)?$/', $areaCode) !== 1) {
                throw new InvalidPopulationStatisticWorkbook("Kode wilayah pada baris {$rowNumber} tidak valid.");
            }

            if ($area === '' || Str::length($area) > 120) {
                throw new InvalidPopulationStatisticWorkbook("Nama wilayah pada baris {$rowNumber} tidak valid.");
            }

            if (isset($codes[$areaCode])) {
                throw new InvalidPopulationStatisticWorkbook("Kode wilayah {$areaCode} muncul lebih dari sekali.");
            }

            $male = $this->populationCount($row['C'] ?? null, $rowNumber, 'L');
            $female = $this->populationCount($row['D'] ?? null, $rowNumber, 'P');
            $total = $this->populationCount($row['E'] ?? null, $rowNumber, 'JML');

            if ($male + $female !== $total) {
                throw new InvalidPopulationStatisticWorkbook("Nilai L + P tidak sama dengan JML pada baris {$rowNumber}.");
            }

            $areaLevel = match (substr_count($areaCode, '.')) {
                1 => AdministrativeAreaLevel::Regency,
                2 => AdministrativeAreaLevel::District,
                3 => AdministrativeAreaLevel::Village,
            };
            $parentAreaCode = match ($areaLevel) {
                AdministrativeAreaLevel::Regency => null,
                AdministrativeAreaLevel::District => '75.01',
                AdministrativeAreaLevel::Village => Str::beforeLast($areaCode, '.'),
                AdministrativeAreaLevel::Legacy => null,
            };

            $codes[$areaCode] = true;
            $normalizedRows[] = [
                'area_code' => $areaCode,
                'area' => $area,
                'area_level' => $areaLevel->value,
                'parent_area_code' => $parentAreaCode,
                'male' => $male,
                'female' => $female,
                'total' => $total,
            ];
        }

        $rowsByCode = collect($normalizedRows)->keyBy('area_code');
        $regencies = $rowsByCode->where('area_level', AdministrativeAreaLevel::Regency->value);
        $districts = $rowsByCode->where('area_level', AdministrativeAreaLevel::District->value);
        $villages = $rowsByCode->where('area_level', AdministrativeAreaLevel::Village->value);

        if ($regencies->count() !== 1 || ! $rowsByCode->has('75.01') || $districts->isEmpty() || $villages->isEmpty()) {
            throw new InvalidPopulationStatisticWorkbook('Workbook harus memuat satu kabupaten 75.01, kecamatan, dan desa/kelurahan.');
        }

        foreach ($normalizedRows as $row) {
            if ($row['parent_area_code'] !== null && ! $rowsByCode->has($row['parent_area_code'])) {
                throw new InvalidPopulationStatisticWorkbook("Induk wilayah {$row['parent_area_code']} tidak ditemukan.");
            }
        }

        $this->assertRollupMatches($districts->values()->all(), $villages->values()->all());
        $this->assertRollupMatches($regencies->values()->all(), $districts->values()->all());

        $regency = $regencies->first();

        return [
            'rows' => $normalizedRows,
            'summary' => [
                'regency_count' => $regencies->count(),
                'district_count' => $districts->count(),
                'village_count' => $villages->count(),
                'male' => $regency['male'],
                'female' => $regency['female'],
                'total' => $regency['total'],
            ],
        ];
    }

    private function populationCount(?string $value, int $rowNumber, string $column): int
    {
        $value = trim((string) $value);

        if (preg_match('/^\d+$/', $value) !== 1
            || strlen($value) > 10
            || (int) $value > self::MAX_POPULATION_COUNT) {
            throw new InvalidPopulationStatisticWorkbook("Kolom {$column} pada baris {$rowNumber} harus bilangan bulat nonnegatif.");
        }

        return (int) $value;
    }

    /**
     * @param  list<array{area_code: string, area: string, area_level: string, parent_area_code: string|null, male: int, female: int, total: int}>  $parents
     * @param  list<array{area_code: string, area: string, area_level: string, parent_area_code: string|null, male: int, female: int, total: int}>  $children
     */
    private function assertRollupMatches(array $parents, array $children): void
    {
        foreach ($parents as $parent) {
            $matchingChildren = collect($children)->where('parent_area_code', $parent['area_code']);

            if ($matchingChildren->isEmpty()
                || $matchingChildren->sum('male') !== $parent['male']
                || $matchingChildren->sum('female') !== $parent['female']
                || $matchingChildren->sum('total') !== $parent['total']) {
                throw new InvalidPopulationStatisticWorkbook("Jumlah wilayah di bawah {$parent['area']} tidak cocok dengan total induknya.");
            }
        }
    }

    private function xml(string $contents): DOMDocument
    {
        if (stripos($contents, '<!DOCTYPE') !== false || stripos($contents, '<!ENTITY') !== false) {
            throw new InvalidPopulationStatisticWorkbook('Deklarasi XML eksternal tidak diizinkan.');
        }

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $loaded = $document->loadXML($contents, LIBXML_NONET | LIBXML_COMPACT | LIBXML_NOBLANKS);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            throw new InvalidPopulationStatisticWorkbook('XML di dalam workbook tidak valid.');
        }

        return $document;
    }

    private function normalizeWorksheetPath(string $target): string
    {
        $target = str_replace('\\', '/', trim($target));
        $segments = str_starts_with($target, '/') ? [] : ['xl'];

        foreach (explode('/', ltrim($target, '/')) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..' || str_contains($segment, "\0")) {
                throw new InvalidPopulationStatisticWorkbook('Lokasi worksheet tidak aman.');
            }

            $segments[] = $segment;
        }

        $path = implode('/', $segments);
        $this->assertSafeArchivePath($path);

        return $path;
    }

    private function assertSafeArchivePath(string $filename): void
    {
        $segments = explode('/', str_replace('\\', '/', $filename));

        if ($filename === ''
            || str_starts_with($filename, '/')
            || preg_match('/^[A-Za-z]:/', $filename) === 1
            || in_array('..', $segments, true)
            || str_contains($filename, "\0")) {
            throw new InvalidPopulationStatisticWorkbook('Arsip XLSX memuat lokasi berkas yang tidak aman.');
        }
    }
}
