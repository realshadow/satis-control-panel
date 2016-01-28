<?php

namespace App\Satis\Model;

use JMS\Serializer\Annotation\Type;

/**
 * Archive Configuration class
 *
 * Represent the archive part, in a satis configuration file
 *
 * @author Lukas Homza <lukashomz@gmail.com>
 */
class ControlPanelConfig {
    /**
     * @Type("boolean")
     */
    private $loaded;

    /**
     * @Type("App\Satis\Model\Config")
     */
    private $config;

    /**
     * @Type("string")
     */
    private $message;
    
    /**
     * @Type("boolean")
     */
    private $locked;

    /**
     * @Type("array")
     */
    private $repository_types;

    /**
     * @Type("array")
     */
    private $node_server;

    /**
     * @param $loaded
     * @return $this
     */
    public function isLoaded($loaded) {
        $this->loaded = $loaded;

        return $this;
    }
    
    /**
     * @param $locked
     * @return $this
     */
    public function isLocked($locked) {
        $this->locked = $locked;

        return $this;
    }    

    /**
     * @param \App\Satis\Model\Config $config
     * @return $this
     */
    public function setConfig(Config $config) {
        $this->config = $config;

        return $this;
    }

    /**
     * @param $message
     * @return $this
     */
    public function setMessage($message) {
        $this->message = $message;

        return $this;
    }

    /**
     * @param array $repositoryTypes
     *
     * @return $this
     */
    public function setRepositoryTypes(array $repositoryTypes) {
        $this->repository_types = $repositoryTypes;

        return $this;
    }

    /**
     * @param mixed $node_server
     * @return ControlPanelConfig
     */
    public function setNodeServer($node_server) {
        $this->node_server = $node_server;

        return $this;
    }
}
