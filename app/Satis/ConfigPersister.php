<?php

namespace App\Satis;

use App\Satis\Model\ConfigLock;
use Illuminate\Filesystem\Filesystem;
use JMS\Serializer\Serializer;

/**
 * @author Lukas Homza <lukashomz@gmail.com>
 */
class ConfigPersister {
    /** @var \Illuminate\Filesystem\Filesystem $filesystem */
	protected $filesystem;
    /** @var \App\Satis\Model\ConfigLock */
    protected $configLock;
    /** @var \App\Satis\ConfigMirror */
    protected $configMirror;
    /** @var \JMS\Serializer\Serializer $serializer */
    protected $serializer;
    /** @var  string $lockFilename */
    protected $lockFilename;

    /**
     * @return \App\Satis\Model\ConfigLock|array|\JMS\Serializer\scalar|mixed|object
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function getLockFile() {
        if($this->filesystem->exists($this->lockFilename)) {
            $configLock = $this->serializer->deserialize(
                $this->filesystem->get($this->lockFilename),
                'App\Satis\Model\ConfigLock',
                'json'
            );
        } else {
            $configLock = new ConfigLock();
        }

        return $configLock;
    }

    /**
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function getLockedRepositories() {
        return $this->getLockFile()->getRepositories();
    }

    /**
     * @param \Illuminate\Filesystem\Filesystem $filesystem
     * @param \App\Satis\Model\ConfigLock $configLock
     * @param \App\Satis\ConfigMirror $configMirror
     * @param \JMS\Serializer\Serializer $serializer
     */
    public function __construct(Filesystem $filesystem, ConfigLock $configLock, ConfigMirror $configMirror,
        Serializer $serializer
    ) {
		$this->filesystem = $filesystem;
        $this->configLock = $configLock;
        $this->configMirror = $configMirror;
        $this->serializer = $serializer;

        $this->lockFilename = config('satis.lock');
        $this->configFile = config('satis.config');
	}

    /**
     * @return bool
     */
    public function isLocked() {
        return $this->getLockFile()->isLocked();
    }

    /**
     * @param string $repositoryId
     */
    public function lock($repositoryId) {
        $lockedRepositories = $this->getLockedRepositories();

        if(!is_null($repositoryId) && !$lockedRepositories->contains($repositoryId)) {
            $lockedRepositories->push($repositoryId);
        }

        $this->configLock
            ->isLocked(true)
            ->since(date('d.m.Y H:i:s'))
            ->by($lockedRepositories);

        $this->filesystem->put(
            $this->lockFilename,
            $this->serializer->serialize($this->configLock, 'json')
        );
    }

    /**
     * @param string|null $repositoryId
     */
    public function unlock($repositoryId = null) {
        $lockedRepositories = $this->getLockedRepositories();

        if($repositoryId !== null && $lockedRepositories->contains($repositoryId)) {
            $lockedRepositories = $lockedRepositories->filter(function($id) use($repositoryId) {
                return $id !== $repositoryId;
            });
        }

        $this->configLock->isLocked(false)->by($lockedRepositories);

        $this->filesystem->put(
            $this->lockFilename,
            $this->serializer->serialize($this->configLock, 'json')
        );
    }

    /**
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function load() {
        $config = $this->filesystem->get($this->configFile);

        return $config;
    }

    /**
     * @param string $config
     */
    public function updateWith($config) {
        $this->filesystem->put($this->configFile, $config);

        $this->filesystem->put(config('satis.public_mirror'), $this->configMirror->getPublicMirror($config));
        $this->filesystem->put(config('satis.private_mirror'), $this->configMirror->getPrivateMirror($config));
    }
}
