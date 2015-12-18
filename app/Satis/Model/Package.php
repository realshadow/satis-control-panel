<?php

namespace App\Satis\Model;

use App\Satis\ConfigManager;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;
use App;

/**
* Package class
*
* @author Lukas Homza <lukashomz@gmail.com>
*/
class Package {
  /**
   * @Type("string")
   */
  private $name;

  /**
   * @Type("string")
   */
  private $version;

  /**
   * Initialize with default opinionated values
   */
  public function __construct() {
    $this->name = '';
    $this->version = '*';
  }

  /**
   * @return string
   */
  public function getVersion() {
    return $this->version;
  }

  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * @param string $version
   */
  public function setVersion($version) {
    $this->version = $version;
  }

  /**
   * @param mixed $name
   * @return Package
   */
  public function setName($name) {
    $this->name = $name;

    return $this;
  }

  /**
   * Get repository ID
   *
   * @return string
   */
  public function getId() {
    return ConfigManager::nameToId($this->name);
  }

}
