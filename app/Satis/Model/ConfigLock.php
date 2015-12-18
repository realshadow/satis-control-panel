<?php
/**
 * Created by PhpStorm.
 * User: lhomza
 * Date: 1. 11. 2015
 * Time: 1:00
 */

namespace App\Satis\Model;

use Illuminate\Support\Collection;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @author Lukas Homza <lukashomz@gmail.com>
 */
class ConfigLock {
    /**
     * @Type("boolean")
     */
    private $locked;

    /**
     * @Type("string")
     */
    private $since;

    /**
     * @Type("array")
     */
    private $repositories;

    /**
     * @param $locked
     * @return $this
     */
    public function isLocked($locked = null) {
        if($locked === null) {
            return $this->locked;
        }

        $this->locked = $locked;

        return $this;
    }

    /**
     * @param $since
     * @return $this
     */
    public function since($since) {
        $this->since = $since;

        return $this;
    }

    /**
     * @param \Illuminate\Support\Collection $repositories
     * @return $this
     */
    public function by(Collection $repositories) {
        $this->repositories = $repositories->values();

        return $this;
    }

    /**
     * @return \Illuminate\Support\Collection $repositories
     */
    public function getRepositories() {
        return new Collection($this->repositories);
    }
}
