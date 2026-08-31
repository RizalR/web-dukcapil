<?php

namespace App\Services;

use App\Exceptions\InvalidThematicStatisticWorkbook;
use DOMDocument;
use DOMElement;
use DOMXPath;

class HtmlTableWorkbookReader
{
    private const int MAX_FILE_BYTES = 1_048_576;

    private const int MAX_ROWS = 1_000;

    private const int MAX_COLUMNS = 100;

    /** @return list<array<string, string|null>> */
    public function read(string $path): array
    {
        $size = @filesize($path);

        if (! is_file($path) || ! is_readable($path) || $size === false || $size > self::MAX_FILE_BYTES) {
            throw new InvalidThematicStatisticWorkbook('File XLS tidak dapat dibaca atau melebihi 1 MB.');
        }

        $contents = file_get_contents($path);

        if ($contents === false || stripos($contents, '<table') === false) {
            throw new InvalidThematicStatisticWorkbook('File XLS bukan tabel HTML yang didukung.');
        }

        if (stripos($contents, '<!DOCTYPE') !== false
            || stripos($contents, '<!ENTITY') !== false
            || stripos($contents, '<script') !== false
            || stripos($contents, '<iframe') !== false
            || stripos($contents, '<object') !== false) {
            throw new InvalidThematicStatisticWorkbook('File XLS memuat elemen aktif atau deklarasi eksternal yang tidak diizinkan.');
        }

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $loaded = $document->loadHTML($contents, LIBXML_NONET | LIBXML_COMPACT | LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            throw new InvalidThematicStatisticWorkbook('Struktur HTML pada file XLS tidak valid.');
        }

        $xpath = new DOMXPath($document);
        $table = $xpath->query('//table')->item(0);

        if (! $table instanceof DOMElement) {
            throw new InvalidThematicStatisticWorkbook('Tabel data tidak ditemukan pada file XLS.');
        }

        $rows = [];

        foreach ($xpath->query('.//tr', $table) ?: [] as $row) {
            $values = [];
            $columnIndex = 1;

            foreach ($xpath->query('./th|./td', $row) ?: [] as $cell) {
                if ($columnIndex > self::MAX_COLUMNS) {
                    throw new InvalidThematicStatisticWorkbook('File XLS memuat lebih dari 100 kolom.');
                }

                $values[$this->columnName($columnIndex)] = trim($cell->textContent);
                $columnIndex++;
            }

            if ($values !== []) {
                $rows[] = $values;
            }

            if (count($rows) > self::MAX_ROWS + 1) {
                throw new InvalidThematicStatisticWorkbook('File XLS memuat lebih dari 1.000 baris data.');
            }
        }

        if (count($rows) < 2) {
            throw new InvalidThematicStatisticWorkbook('File XLS tidak memiliki baris data.');
        }

        return $rows;
    }

    private function columnName(int $index): string
    {
        $name = '';

        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)).$name;
            $index = intdiv($index, 26);
        }

        return $name;
    }
}
