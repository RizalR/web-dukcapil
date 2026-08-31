<?php

namespace App\Logging;

use InvalidArgumentException;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use RuntimeException;

class SizeRotatingFileHandler extends AbstractProcessingHandler
{
    public function __construct(
        private readonly string $stream,
        private readonly int $maxBytes = 10 * 1024 * 1024,
        private readonly int $maxFiles = 14,
        int|string|Level $level = Level::Debug,
        bool $bubble = true,
        private readonly ?int $filePermission = null,
    ) {
        if ($this->maxBytes < 1) {
            throw new InvalidArgumentException('The maximum log file size must be at least one byte.');
        }

        if ($this->maxFiles < 1) {
            throw new InvalidArgumentException('At least one log file must be retained.');
        }

        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        $contents = (string) $record->formatted;

        if ($contents === '') {
            return;
        }

        $this->ensureDirectoryExists();
        $lock = fopen($this->stream.'.lock', 'c+b');

        if ($lock === false) {
            throw new RuntimeException("Unable to open the log rotation lock file [{$this->stream}.lock].");
        }

        try {
            if (! flock($lock, LOCK_EX)) {
                throw new RuntimeException("Unable to lock the log file [{$this->stream}].");
            }

            $this->writeWithinLimit($contents);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function writeWithinLimit(string $contents): void
    {
        while ($contents !== '') {
            $currentSize = $this->fileSize();
            $contentsLength = strlen($contents);

            if ($currentSize >= $this->maxBytes) {
                $this->rotate();
                $currentSize = 0;
            }

            $remainingBytes = $this->maxBytes - $currentSize;

            if ($contentsLength <= $this->maxBytes && $contentsLength > $remainingBytes && $currentSize > 0) {
                $this->rotate();

                continue;
            }

            $chunkLength = min($contentsLength, $remainingBytes);
            $this->append(substr($contents, 0, $chunkLength));
            $contents = substr($contents, $chunkLength);

            if ($contents !== '') {
                $this->rotate();
            }
        }
    }

    private function append(string $contents): void
    {
        $isNewFile = ! is_file($this->stream);
        $file = fopen($this->stream, 'ab');

        if ($file === false) {
            throw new RuntimeException("Unable to open the log file [{$this->stream}] for writing.");
        }

        try {
            $remaining = $contents;

            while ($remaining !== '') {
                $writtenBytes = fwrite($file, $remaining);

                if ($writtenBytes === false || $writtenBytes === 0) {
                    throw new RuntimeException("Unable to write to the log file [{$this->stream}].");
                }

                $remaining = substr($remaining, $writtenBytes);
            }
        } finally {
            fclose($file);
        }

        if ($isNewFile && $this->filePermission !== null) {
            chmod($this->stream, $this->filePermission);
        }
    }

    private function rotate(): void
    {
        $this->removeArchivesOutsideRetention();
        $archiveCount = $this->maxFiles - 1;

        if ($archiveCount === 0) {
            $this->deleteIfExists($this->stream);

            return;
        }

        $this->deleteIfExists($this->archivePath($archiveCount));

        for ($archive = $archiveCount - 1; $archive >= 1; $archive--) {
            $this->moveIfExists($this->archivePath($archive), $this->archivePath($archive + 1));
        }

        $this->moveIfExists($this->stream, $this->archivePath(1));
        clearstatcache(true, $this->stream);
    }

    private function removeArchivesOutsideRetention(): void
    {
        foreach (glob($this->stream.'.*') ?: [] as $path) {
            if (preg_match('/\.(\d+)$/', $path, $matches) === 1 && (int) $matches[1] >= $this->maxFiles) {
                $this->deleteIfExists($path);
            }
        }
    }

    private function deleteIfExists(string $path): void
    {
        if (is_file($path) && ! unlink($path)) {
            throw new RuntimeException("Unable to delete the expired log file [{$path}].");
        }
    }

    private function moveIfExists(string $from, string $to): void
    {
        if (is_file($from) && ! rename($from, $to)) {
            throw new RuntimeException("Unable to rotate the log file [{$from}] to [{$to}].");
        }
    }

    private function archivePath(int $archive): string
    {
        return $this->stream.'.'.$archive;
    }

    private function fileSize(): int
    {
        clearstatcache(true, $this->stream);
        $size = is_file($this->stream) ? filesize($this->stream) : 0;

        return $size === false ? 0 : $size;
    }

    private function ensureDirectoryExists(): void
    {
        $directory = dirname($this->stream);

        if (! is_dir($directory) && ! mkdir($directory, 0777, true) && ! is_dir($directory)) {
            throw new RuntimeException("Unable to create the log directory [{$directory}].");
        }
    }
}
