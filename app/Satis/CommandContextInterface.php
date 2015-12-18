<?php

namespace App\Satis;

/**
 * @author Lukas Homza <lukashomz@gmail.com>
 */
interface CommandContextInterface {
    /**
     * @param string $logFile
     * @return string
     */
    public function getOutputRedirection($logFile);

	/**
     * @return string
     */
    public function getShouldUnlockOnCompletion();

    /**
     * @return \Monolog\Logger
     * @throws \Exception
     */
    public function getLogger();
}
