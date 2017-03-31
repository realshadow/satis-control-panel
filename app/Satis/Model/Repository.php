<?php

namespace App\Satis\Model;

use App\Satis\ConfigManager;
use JMS\Serializer\Annotation\Type;

/**
* Repository class
*
* Represent a composer repository definition
* @author Lukas Homza <lukashomz@gmail.com>
*/
class Repository {

  const REGEX = '#((git|ssh|http(s)?)|(git@[\w.]+))(:(\/\/)?)([\w.@:\/\-~]+)(.git)(\/)?#';

  /**
   * @Type("string")
   */
  private $type;

  /**
   * @Type("string")
   */
  private $url;

  /**
   * Initialize with default opinionated values
   */
  public function __construct() {
    $this->type = config('satis.default_repository_type');
    $this->url = '';
  }

  /**
   * Get the string representation
   *
   * @return string
   */
  public function __toString() {
    return $this->url;
  }

  /**
   * Get type
   *
   * @return string $type
   */
  public function getType() {
    return $this->type;
  }

  /**
   * Set type
   *
   * @param string $type
   *
   * @return static
   */
  public function setType($type) {
    $this->type = strtolower($type);

    return $this;
  }

  /**
   * Get url
   *
   * @return string $url
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * Set url
   *
   * @param string $url
   *
   * @return static
   */
  public function setUrl($url) {
    $this->url = urldecode(strtolower($url));

    return $this;
  }

  /**
   * Get repository ID
   *
   * @return string
   */
  public function getId() {
    return ConfigManager::nameToId($this->url);
  }
}
