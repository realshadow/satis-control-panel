<?php

namespace App\Satis;

/**
 * Class CommandContextInterface
 * @package App\Satis
 */
abstract class BuildContext {
    /** @var string $itemId */
    protected $itemId;
    /** @var string $itemName */
    protected $itemName;

	/**
     * @return int
     */
    abstract public function getType();

    /**
     * @return string
     */
    abstract public function getBuildDirectory();

    /**
     * @return string
     */
    abstract public function getConfigFile();

    /**
     * @return string
     */
    public function getItemId() {
        return $this->itemId;
    }

    /**
     * @param string $itemId
     * @return BuildContext
     */
    public function setItemId($itemId) {
        $this->itemId = $itemId;

        return $this;
    }

    /**
     * @return string
     */
    public function getItemName() {
        return $this->itemName;
    }

    /**
     * @param string $itemName
     * @return BuildContext
     */
    public function setItemName($itemName) {
        $this->itemName = $itemName;

        return $this;
    }
}
