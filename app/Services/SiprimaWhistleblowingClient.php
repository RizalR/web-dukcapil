<?php

namespace App\Services;

use App\Exceptions\InvalidWhistleblowingSnapshot;
use DateTimeImmutable;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class SiprimaWhistleblowingClient
{
    /**
     * @return array{
     *     generated_at: string,
     *     items: list<array{
     *         publication_id: string,
     *         category_code: string,
     *         category_label: string,
     *         summary: string,
     *         follow_up_result: string,
     *         published_at: string,
     *         updated_at: string
     *     }>
     * }
     */
    public function fetchSnapshot(): array
    {
        $url = trim((string) config('services.siprima_whistleblowing.url'));
        $token = trim((string) config('services.siprima_whistleblowing.token'));
        if ($url === '' || $token === '') {
            throw new RuntimeException('Konfigurasi integrasi whistleblowing belum lengkap.');
        }
        if (app()->isProduction() && ! str_starts_with($url, 'https://')) {
            throw new RuntimeException('Endpoint whistleblowing produksi wajib menggunakan HTTPS.');
        }

        $response = Http::acceptJson()
            ->withToken($token)
            ->withUserAgent('Dukcapil-Whistleblowing-Sync/1.0')
            ->connectTimeout((int) config('services.siprima_whistleblowing.connect_timeout', 3))
            ->timeout((int) config('services.siprima_whistleblowing.timeout', 10))
            ->retry([200, 500], throw: false)
            ->get($url);

        if ($response->failed()) {
            throw new RuntimeException('SI PRIMA mengembalikan status HTTP '.$response->status().'.');
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            throw new InvalidWhistleblowingSnapshot('Respons SI PRIMA bukan objek JSON.');
        }

        return $this->validateSnapshot($payload);
    }

    /**
     * @param  array<mixed>  $payload
     * @return array{
     *     generated_at: string,
     *     items: list<array{
     *         publication_id: string,
     *         category_code: string,
     *         category_label: string,
     *         summary: string,
     *         follow_up_result: string,
     *         published_at: string,
     *         updated_at: string
     *     }>
     * }
     */
    private function validateSnapshot(array $payload): array
    {
        $this->assertExactKeys($payload, ['schema_version', 'generated_at', 'data', 'meta'], 'snapshot');
        if (($payload['schema_version'] ?? null) !== 1) {
            throw new InvalidWhistleblowingSnapshot('Versi skema snapshot tidak didukung.');
        }

        $generatedAt = $this->validatedDate($payload['generated_at'] ?? null, 'generated_at');
        $data = $payload['data'] ?? null;
        $meta = $payload['meta'] ?? null;
        if (! is_array($data) || ! array_is_list($data) || count($data) > 5000) {
            throw new InvalidWhistleblowingSnapshot('Daftar publikasi tidak valid.');
        }
        if (! is_array($meta)) {
            throw new InvalidWhistleblowingSnapshot('Metadata snapshot tidak valid.');
        }

        $this->assertExactKeys($meta, ['count', 'complete'], 'meta');
        if (($meta['complete'] ?? null) !== true || ($meta['count'] ?? null) !== count($data)) {
            throw new InvalidWhistleblowingSnapshot('Snapshot tidak lengkap.');
        }

        $publicationIds = [];
        $items = [];
        foreach ($data as $index => $item) {
            if (! is_array($item)) {
                throw new InvalidWhistleblowingSnapshot("Publikasi ke-{$index} tidak valid.");
            }
            $this->assertExactKeys($item, [
                'publication_id',
                'category_code',
                'category_label',
                'summary',
                'follow_up_result',
                'published_at',
                'updated_at',
            ], "publikasi ke-{$index}");

            $publicationId = $this->validatedString($item['publication_id'] ?? null, 'publication_id', 28, 28);
            if (preg_match('/^WBP-[A-F0-9]{24}$/', $publicationId) !== 1 || isset($publicationIds[$publicationId])) {
                throw new InvalidWhistleblowingSnapshot('ID publikasi tidak valid atau duplikat.');
            }
            $publicationIds[$publicationId] = true;

            $categoryCode = $this->validatedString($item['category_code'] ?? null, 'category_code', 1, 64);
            if (preg_match('/^[a-z0-9_]+$/', $categoryCode) !== 1) {
                throw new InvalidWhistleblowingSnapshot('Kode kategori tidak valid.');
            }

            $summary = $this->validatedString($item['summary'] ?? null, 'summary', 30, 1000);
            $followUpResult = $this->validatedString($item['follow_up_result'] ?? null, 'follow_up_result', 30, 3000);
            if ($this->containsSensitiveData($summary."\n".$followUpResult)) {
                throw new InvalidWhistleblowingSnapshot('Teks publik terdeteksi mengandung data pribadi.');
            }

            $items[] = [
                'publication_id' => $publicationId,
                'category_code' => $categoryCode,
                'category_label' => $this->validatedString($item['category_label'] ?? null, 'category_label', 1, 120),
                'summary' => $summary,
                'follow_up_result' => $followUpResult,
                'published_at' => $this->validatedDate($item['published_at'] ?? null, 'published_at'),
                'updated_at' => $this->validatedDate($item['updated_at'] ?? null, 'updated_at'),
            ];
        }

        return ['generated_at' => $generatedAt, 'items' => $items];
    }

    /** @param array<mixed> $value */
    private function assertExactKeys(array $value, array $expectedKeys, string $field): void
    {
        $actualKeys = array_keys($value);
        sort($actualKeys);
        sort($expectedKeys);
        if ($actualKeys !== $expectedKeys) {
            throw new InvalidWhistleblowingSnapshot("Struktur {$field} tidak sesuai kontrak.");
        }
    }

    private function validatedString(mixed $value, string $field, int $minimum, int $maximum): string
    {
        if (! is_string($value)) {
            throw new InvalidWhistleblowingSnapshot("Field {$field} harus berupa teks.");
        }

        $length = mb_strlen($value);
        if ($length < $minimum || $length > $maximum) {
            throw new InvalidWhistleblowingSnapshot("Panjang field {$field} tidak valid.");
        }

        return $value;
    }

    private function validatedDate(mixed $value, string $field): string
    {
        if (! is_string($value) || $value === '') {
            throw new InvalidWhistleblowingSnapshot("Field {$field} harus berupa tanggal.");
        }

        try {
            return (new DateTimeImmutable($value))->format(DATE_ATOM);
        } catch (Throwable) {
            throw new InvalidWhistleblowingSnapshot("Field {$field} berisi tanggal yang tidak valid.");
        }
    }

    private function containsSensitiveData(string $text): bool
    {
        if (preg_match('/\b\d{16}\b/u', $text) === 1
            || preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/iu', $text) === 1) {
            return true;
        }

        $normalized = preg_replace('/[\s().-]+/', '', $text);

        return is_string($normalized)
            && preg_match('/(?<!\d)(?:\+62|62|0)8\d{8,12}(?!\d)/u', $normalized) === 1;
    }
}
