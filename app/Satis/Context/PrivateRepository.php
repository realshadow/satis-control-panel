<?php

namespace App\Satis\Context;

use App\Satis\BuildContext;
use App\Satis\ConfigBuilder;

/**
 * @author Lukas Homza <lukashomz@gmail.com>
 */
class PrivateRepository extends BuildContext {
	/**
     * @return int
     */
    public function getType() {
        return ConfigBuilder::PRIVATE_REPOSITORY;
    }

	/**
     * @return string
     */
    public function getConfigFile() {
        return config('satis.private_mirror');
    }

    /**
     * @return string
     */
    public function getBuildDirectory() {
        return config('satis.private_repository');
    }
}
