<?php

namespace App\Satis\Context;

use App\Satis\CommandContextInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * @author Lukas Homza <lukashomz@gmail.com>
 */
class AsyncCommand implements CommandContextInterface {
    /**
     * @param string $logFile
     * @return string
     */
    public function getOutputRedirection($logFile) {
        return '&> ' . escapeshellarg($logFile);
    }

	/**
     * @return string
     */
    public function getShouldUnlockOnCompletion() {
        return ' && php artisan satis:persister:unlock';
    }

    /**
     * @return \Monolog\Logger
     */
    public function getLogger() {
        $handler = new StreamHandler(storage_path('logs/builder_async.log'), Logger::DEBUG);
        $handler->setFormatter(new LineFormatter(null, null, true, true));

        $logger = new Logger('AsyncBuildLog');
        $logger->pushHandler($handler);

        return $logger;
    }
}
