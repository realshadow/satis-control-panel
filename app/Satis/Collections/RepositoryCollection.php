<?php

namespace App\Satis\Collections;

use \InvalidArgumentException;
use App\Satis\Model\Repository;
use Illuminate\Support\Collection;

/**
 * @author Lukas Homza <lukashomz@gmail.com>
 */
class RepositoryCollection extends Collection {
    /**
     * Put an item in the collection by key.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return $this
     */
    public function put($key, $value) {
        if(!$value instanceof Repository) {
            throw new InvalidArgumentException('RepositoryCollection accepts only elements of "Repository" type.');
        }

        return parent::put($key, $value);
    }
}
