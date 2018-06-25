<?php

namespace App\Satis;

use App\Satis\Collections\PackageCollection;
use App\Satis\Collections\RepositoryCollection;
use App\Satis\Model\Repository;
use JMS\Serializer\Serializer;

/**
 * @author Lukas Homza <lukashomz@gmail.com>
 */
class ConfigMirror {
    /** @var \App\Satis\Model\ConfigLock */
    protected $serializer;

	/**
     * ConfigMirror constructor.
     * @param \JMS\Serializer\Serializer $serializer
     */
    public function __construct(Serializer $serializer) {
        $this->serializer = $serializer;
    }

	/**
     * @param string $config
     * @return string
     */
    public function getPublicMirror($config) {
        /** @var \App\Satis\Model\Config $publicConfig */
        $publicConfig = $this->serializer->deserialize(
            $config,
            'App\Satis\Model\Config',
            'json'
        );

        $repository = new Repository();
        $repository->setType('composer');
        $repository->setUrl('https://packagist.org/');

        $homepage = $publicConfig->getHomepage();

        $publicConfig->setHomepage(rtrim($homepage, '/') . '/' . config('satis.public_repository'))
            ->setRequireAll(false)
            ->setRepositories(new RepositoryCollection([$repository]));

        return $this->serializer->serialize($publicConfig, 'json');
    }

    /**
     * @param string $config
     * @return string
     */
    public function getPrivateMirror($config) {
        /** @var \App\Satis\Model\Config $privateConfig */
        $privateConfig = $this->serializer->deserialize(
            $config,
            'App\Satis\Model\Config',
            'json'
        );

        $homepage = $privateConfig->getHomepage();

        $privateConfig->setHomepage(rtrim($homepage, '/') . '/' . config('satis.private_repository'))
            ->setRequireAll(true)
            ->setProviders(true)
            ->setPackages(new PackageCollection());

        return $this->serializer->serialize($privateConfig, 'json');
    }
}
