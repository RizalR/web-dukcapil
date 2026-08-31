<?php

namespace Tests\Feature;

use App\Logging\SizeRotatingFileHandler;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class LoggingConfigurationTest extends TestCase
{
    public function test_single_channel_uses_size_limited_rotation(): void
    {
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'dukcapil-log-config-'.bin2hex(random_bytes(8));
        $path = $directory.DIRECTORY_SEPARATOR.'laravel.log';
        mkdir($directory, 0777, true);

        try {
            config()->set('logging.channels.single.handler_with', [
                'stream' => $path,
                'maxBytes' => 256,
                'maxFiles' => 3,
            ]);
            Log::forgetChannel('single');

            $logger = Log::channel('single');
            $logger->info(str_repeat('A', 180));
            $logger->info(str_repeat('B', 180));

            $this->assertInstanceOf(SizeRotatingFileHandler::class, $logger->getHandlers()[0]);
            $this->assertFileExists($path.'.1');

            foreach (glob($path.'*') ?: [] as $logFile) {
                if (! str_ends_with($logFile, '.lock')) {
                    $this->assertLessThanOrEqual(256, filesize($logFile));
                }
            }
        } finally {
            Log::forgetChannel('single');

            foreach (glob($directory.DIRECTORY_SEPARATOR.'*') ?: [] as $file) {
                unlink($file);
            }

            rmdir($directory);
        }
    }
}
