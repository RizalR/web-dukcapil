<?php

namespace Tests\Unit\Support;

use App\Support\AdministrativeAreaCodeNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AdministrativeAreaCodeNormalizerTest extends TestCase
{
    #[DataProvider('areaCodes')]
    public function test_it_normalizes_supported_area_codes(string $input, string $expected): void
    {
        $this->assertSame($expected, (new AdministrativeAreaCodeNormalizer)->normalize($input));
    }

    /** @return iterable<string, array{string, string}> */
    public static function areaCodes(): iterable
    {
        yield 'regency without dots' => ['7501', '75.01'];
        yield 'district without dots' => ['750101', '75.01.01'];
        yield 'village without dots' => ['7501011001', '75.01.01.1001'];
        yield 'regency with dots' => ['75.01', '75.01'];
        yield 'district with dots' => ['75.01.01', '75.01.01'];
        yield 'village with dots' => ['75.01.01.1001', '75.01.01.1001'];
        yield 'surrounding spaces' => [' 7501 ', '75.01'];
        yield 'invalid length remains invalid' => ['75010', '75010'];
    }
}
