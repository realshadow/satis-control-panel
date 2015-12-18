<?php

namespace App\Satis\Collections;

use App\Satis\Model\Package;
use \InvalidArgumentException;
use Illuminate\Support\Collection;

/**
 * @author Lukas Homza <lukashomz@gmail.com>
 */
class PackageCollection extends Collection {
    /**
     * Put an item in the collection by key.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return $this
     */
    public function put($key, $value) {
        if(!$value instanceof Package) {
            throw new InvalidArgumentException('PackageCollection accepts only elements of "Package" type.');
        }

        return parent::put($key, $value);
    }
}
