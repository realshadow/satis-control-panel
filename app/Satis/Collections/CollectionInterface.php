<?php

namespace App\Satis\Collections;

/**
 * @author Lukas Homza <lukashomz@gmail.com>
 */
interface CollectionInterface {
    /**
     * Put an item in the collection by key.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return $this
     */
    public function put($key, $value);

    /**
     * @return string
     */
    public function getId();
}
