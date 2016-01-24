<?php

namespace App\Satis;

use App\Satis\Collections\PackageCollection;
use App\Satis\Context\PrivateRepository;
use App\Satis\Context\PublicRepository;
use App\Satis\Exceptions\PackageNotFoundException;
use App\Satis\Model\Package;
use BadMethodCallException;
use App\Satis\Collections\RepositoryCollection;
use App\Satis\Exceptions\RepositoryNotFoundException;
use App\Satis\Model\Repository;
use Illuminate\Support\Collection;
use JMS\Serializer\Serializer;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * @method string addOrUpdateRepository($repositoryId, Collection $repositoryData);
 * @method string addOrUpdatePackage($packageId, Collection $packageData);
 * @method void deleteRepository($repositoryId);
 * @method void deletePackage($packageId);
 * @property \App\Satis\Model\Config $satis;
 *
 * @author Lukas Homza <lukashomz@gmail.com>
 */
class ConfigManager {
    const BUILD_PRIVATE = 1;
    const BUILD_PUBLIC = 2;

    /** @var \App\Satis\ConfigPersister $configPersister */
    protected $configPersister;
    /** @var \App\Satis\ConfigBuilder $configBuilder */
    protected $configBuilder;
    /** @var \JMS\Serializer\Serializer $serializer */
    protected $serializer;
    /** @var \App\Satis\Model\Config $_satis */
    protected $_satis;

    /** @var bool $disableBuild */
    protected $disableBuild = false;

    /** @var string  */
    protected $hiddenEntityPrefix = '_';

    /** @var array  */
    protected $lazyLoadedProperties = [
        '_satis' => 'loadSatisConfig'
    ];

    /**
     * @return \App\Satis\Model\Config
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function loadSatisConfig() {
        return $this->serializer->deserialize(
            $this->configPersister->load(),
            'App\Satis\Model\Config',
            'json'
        );
    }

    /**
     * @return \Monolog\Logger
     */
    protected function getLogger() {
        $handler = new StreamHandler(storage_path('logs/api_request.log'), Logger::DEBUG);
        $handler->setFormatter(new LineFormatter(null, null, true, true));

        $logger = new Logger('ApiRequestLog');
        $logger->pushHandler($handler);

        return $logger;
    }

    /**
     * @param \App\Satis\Collections\RepositoryCollection $repositoryCollection
     * @param string $repositoryId
     * @param \Illuminate\Support\Collection $input
     * @return \App\Satis\Collections\RepositoryCollection
     * @throws \App\Satis\Exceptions\RepositoryNotFoundException
     */
    protected function _addOrUpdateRepository(RepositoryCollection $repositoryCollection, $repositoryId,
        Collection $input
    ) {
        $repository = new Repository();
        $repository->setUrl($input->get('url'));
        $repository->setType($input->get('type', config('satis.default_repository_type')));

        if($input->has('async_mode')) {
            $this->configBuilder->setAsyncMode(filter_var($input->get('async_mode'), FILTER_VALIDATE_BOOLEAN));
        }

        if($input->has('disable_build')) {
            $this->setDisableBuild(filter_var($input->get('disable_build'), FILTER_VALIDATE_BOOLEAN));
        }

        if($repositoryId !== null) {
            if($repositoryCollection->has($repositoryId)) {
                $repositoryCollection->put($repositoryId, $repository);
            } else {
                throw new RepositoryNotFoundException('Repository with ID "' . $repositoryId . '" does not exist.');
            }
        } else {
            $repositoryId = static::nameToId($repository->getUrl());

            $repositoryCollection->put($repositoryId, $repository);
        }

        $buildContext = new PrivateRepository();
        $buildContext->setItemId($repositoryId)->setItemName($repository->getUrl());

        $this->configBuilder->setBuildContext($buildContext);

        return $repositoryCollection;
    }

    /**
     * @param \App\Satis\Collections\PackageCollection $packageCollection
     * @param string $packageId
     * @param \Illuminate\Support\Collection $input
     * @return \App\Satis\Collections\RepositoryCollection
     * @throws \App\Satis\Exceptions\PackageNotFoundException
     */
    protected function _addOrUpdatePackage(PackageCollection $packageCollection, $packageId,
        Collection $input
    ) {
        $package = new Package();
        $package->setName($input->get('name'));

        if($input->has('async_mode')) {
            $this->configBuilder->setAsyncMode(filter_var($input->get('async_mode'), FILTER_VALIDATE_BOOLEAN));
        }

        if($input->has('disable_build')) {
            $this->setDisableBuild(filter_var($input->get('disable_build'), FILTER_VALIDATE_BOOLEAN));
        }

        if($packageId !== null) {
            if($packageCollection->has($packageId)) {
                $packageCollection->put($packageId, $package);
            } else {
                throw new PackageNotFoundException('Package with ID "' . $packageId . '" does not exist.');
            }
        } else {
            $packageId = static::nameToId($package->getName());

            $packageCollection->put($packageId, $package);
        }

        $buildContext = new PublicRepository();
        $buildContext->setItemId($packageId)->setItemName($package->getName());

        $this->configBuilder->setBuildContext($buildContext);

        return $packageCollection;
    }

    /**
     * @param \App\Satis\Collections\RepositoryCollection $repositoryCollection
     * @param string $repositoryId
     * @return \App\Satis\Collections\RepositoryCollection
     * @throws \App\Satis\Exceptions\RepositoryNotFoundException
     */
    protected function _deleteRepository(RepositoryCollection $repositoryCollection, $repositoryId) {
        if($repositoryCollection->has($repositoryId)) {
            $repositoryCollection->forget($repositoryId);
        } else {
            throw new RepositoryNotFoundException('Repository with ID "' . $repositoryId . '" does not exist.');
        }

        $this->setDisableBuild(true);

        return $repositoryCollection;
    }

    /**
     * @param \App\Satis\Collections\PackageCollection $packageCollection
     * @param string $packageId
     * @return \App\Satis\Collections\RepositoryCollection
     * @throws \App\Satis\Exceptions\RepositoryNotFoundException
     */
    protected function _deletePackage(PackageCollection $packageCollection, $packageId) {
        if($packageCollection->has($packageId)) {
            $packageCollection->forget($packageId);
        } else {
            throw new RepositoryNotFoundException('Package with ID "' . $packageId . '" does not exist.');
        }

        $this->setDisableBuild(true);

        return $packageCollection;
    }

    /**
     * @return string
     */
    protected function save() {
        $satisConfig = $this->serializer->serialize($this->satis, 'json');

        $this->configPersister->updateWith($satisConfig);

        if($this->disableBuild === false) {
            return $this->configBuilder->build();
        }

        return true;
    }

    /**
     * @param string $repositoryUrl
     * @return string
     */
    public static function nameToId($repositoryUrl) {
        return md5($repositoryUrl);
    }

    /**
     * @param \App\Satis\ConfigPersister $configPersister
     * @param ConfigBuilder $configBuilder
     * @param \JMS\Serializer\Serializer $serializer
     */
    public function __construct(ConfigPersister $configPersister, ConfigBuilder $configBuilder,
                                Serializer $serializer
    ) {
        $this->configPersister = $configPersister;
        $this->configBuilder = $configBuilder;
        $this->serializer = $serializer;
    }

    /**
     * @param string $method
     * @param array $arguments
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function __call($method, array $arguments) {
        $method = $this->hiddenEntityPrefix . $method;

        if(method_exists($this, $method)) {
            $logger = $this->getLogger();

            $logger->info(str_repeat('=', 30));
            $logger->info('Request => ' . \Request::method() . ' (' . $method .') => '. json_encode($arguments));

            $type = str_plural(last(explode('_', snake_case($method))), 2);

            array_unshift($arguments, $this->satis->{'get' . ucfirst($type)}());

            try {
                $items = call_user_func_array([$this, $method], $arguments);
            } catch(\Exception $e) {
                $logger->info('Response => ' . $e->getMessage());
                $logger->info(str_repeat('=', 30));

                throw $e;
            }

            $this->satis->{'set' . $type}($items);

            return $this->save();
        } else {
            throw new BadMethodCallException('Called method "' . $method . '" does not exist.');
        }
    }

    /**
     * @param string $property
     * @return mixed
     */
    public function __get($property) {
        $property = $this->hiddenEntityPrefix.$property;

        if(isset($this->lazyLoadedProperties[$property])) {
            $this->{$property} = $this->{$this->lazyLoadedProperties[$property]}();
            
            unset($this->lazyLoadedProperties[$property]);
        }

        return $this->{$property};
    }

    /**
     * @param boolean $disableBuild
     * @return ConfigManager
     */
    public function setDisableBuild($disableBuild) {
        $this->disableBuild = $disableBuild;

        return $this;
    }

    /**
     * @param string|null $repositoryId
     * @return string
     * @throws \App\Satis\Exceptions\RepositoryNotFoundException
     */
    public function getRepositories($repositoryId = null) {
        $repositoryCollection = $this->satis->getRepositories();

        if($repositoryId !== null) {
            if(!$repositoryCollection->has($repositoryId)) {
                throw new RepositoryNotFoundException('Repository with ID "' . $repositoryId . '" does not exist.');
            }

            $output = $repositoryCollection->get($repositoryId);
        } else {
            $output = $repositoryCollection;
        }

        return $this->serializer->serialize($output, 'json');
    }

	/**
     * @param string|null $packageId
     * @return string
     * @throws \App\Satis\Exceptions\PackageNotFoundException
     */
    public function getPackages($packageId = null) {
        $packageCollection = $this->satis->getPackages();

        if($packageId !== null) {
            if(!$packageCollection->has($packageId)) {
                throw new PackageNotFoundException('Package with ID "' . $packageId . '" does not exist.');
            }

            $output = $packageCollection->get($packageId);
        } else {
            $output = $packageCollection;
        }

        return $this->serializer->serialize($output, 'json');
    }

	/**
     * @param \App\Satis\BuildContext $buildContext
     */
    public function forceBuild(BuildContext $buildContext) {
        if($buildContext->getItemName() !== null) {
            $buildContext->setItemId(self::nameToId($buildContext->getItemName()));
        }

        $this->configBuilder
            ->setBuildContext($buildContext)
            ->build();
    }

    /**
     * @return bool
     */
    public function isBuilding() {
        return $this->configPersister->isLocked();
    }

    /**
     * @return \App\Satis\Model\Config
     */
    public function getDefinition() {
        return $this->satis;
    }
}
