<?php

namespace App\Satis\Context;

use App\Satis\BuildContext;
use App\Satis\ConfigBuilder;

/**
 * @author Lukas Homza <lukashomz@gmail.com>
 */
class PublicRepository extends BuildContext {
    /**
     * @return int
     */
    public function getType() {
        return ConfigBuilder::PUBLIC_REPOSITORY;
    }

    /**
     * @return string
     */
    public function getConfigFile() {
        return config('satis.public_mirror');
    }

    /**
     * @return string
     */
    public function getBuildDirectory() {
        return config('satis.public_repository');
    }
}
