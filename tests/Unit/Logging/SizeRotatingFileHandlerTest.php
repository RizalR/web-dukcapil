<?php

namespace Tests\Unit\Logging;

use App\Logging\SizeRotatingFileHandler;
use InvalidArgumentException;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class SizeRotatingFileHandlerTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'dukcapil-log-'.bin2hex(random_bytes(8));
        mkdir($this->directory, 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory.DIRECTORY_SEPARATOR.'*') ?: [] as $path) {
            unlink($path);
        }

        rmdir($this->directory);

        parent::tearDown();
    }

    public function test_rotates_before_a_record_would_exceed_the_file_limit(): void
    {
        $path = $this->directory.DIRECTORY_SEPARATOR.'laravel.log';
        $logger = $this->logger($path, 100, 3);

        $logger->info(str_repeat('A', 80));
        $logger->info(str_repeat('B', 30));

        $this->assertSame(str_repeat('B', 30), file_get_contents($path));
        $this->assertSame(str_repeat('A', 80), file_get_contents($path.'.1'));
        $this->assertLessThanOrEqual(100, filesize($path));
        $this->assertLessThanOrEqual(100, filesize($path.'.1'));
    }

    public function test_removes_the_oldest_archive_when_retention_is_reached(): void
    {
        $path = $this->directory.DIRECTORY_SEPARATOR.'laravel.log';
        $logger = $this->logger($path, 64, 3);

        foreach (['first', 'second', 'third', 'fourth'] as $message) {
            $logger->info(str_pad($message, 40, '-'));
        }

        $this->assertSame(str_pad('fourth', 40, '-'), file_get_contents($path));
        $this->assertSame(str_pad('third', 40, '-'), file_get_contents($path.'.1'));
        $this->assertSame(str_pad('second', 40, '-'), file_get_contents($path.'.2'));
        $this->assertFileDoesNotExist($path.'.3');
    }

    public function test_splits_an_oversized_record_without_exceeding_the_limit(): void
    {
        $path = $this->directory.DIRECTORY_SEPARATOR.'laravel.log';
        $logger = $this->logger($path, 64, 4);

        $logger->info(str_repeat('X', 150));

        $this->assertSame(22, filesize($path));
        $this->assertSame(64, filesize($path.'.1'));
        $this->assertSame(64, filesize($path.'.2'));
        $this->assertSame(str_repeat('X', 150), file_get_contents($path.'.2').file_get_contents($path.'.1').file_get_contents($path));
    }

    public function test_rejects_a_zero_byte_file_limit(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SizeRotatingFileHandler($this->directory.DIRECTORY_SEPARATOR.'laravel.log', 0);
    }

    private function logger(string $path, int $maxBytes, int $maxFiles): Logger
    {
        $handler = new SizeRotatingFileHandler($path, $maxBytes, $maxFiles);
        $handler->setFormatter(new LineFormatter('%message%', null, true, true));

        return new Logger('test', [$handler]);
    }
}
