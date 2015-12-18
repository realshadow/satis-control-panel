<?php

namespace App\Providers;

use App\Satis\Collections\PackageCollection;
use App\Satis\Collections\RepositoryCollection;
use App\Satis\ConfigBuilder;
use App\Satis\ConfigManager;
use App\Satis\ConfigMirror;
use App\Satis\ConfigPersister;
use App\Satis\Model\ConfigLock;
use App\Satis\Model\Package;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use JMS\Serializer\Context;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
use Doctrine\Common\Annotations\AnnotationRegistry;
use JMS\Serializer\VisitorInterface;

/**
 * @author Lukas Homza <lukashomz@gmail.com>
 */
class SatisServiceProvider extends ServiceProvider {
	/**
     * @return void
     */
    public function boot() {
        AnnotationRegistry::registerAutoloadNamespace(
            'JMS\Serializer\Annotation', base_path('vendor/jms/serializer/src')
        );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        $serializer = SerializerBuilder::create()
            ->configureHandlers(function(HandlerRegistry $registry) {
                $registry->registerHandler(
                    'serialization',
                    'App\Satis\Collections\RepositoryCollection',
                    'json',
                    function(VisitorInterface $visitor, Collection $collection, array $type, Context $context) {
                        return $visitor->visitArray($collection->values(), $type, $context);
                    }
                );

                $registry->registerHandler(
                    'serialization',
                    'App\Satis\Collections\PackageCollection',
                    'json',
                    function(VisitorInterface $visitor, Collection $collection, array $type, Context $context) {
                        $output = [];
                        foreach($collection->values() as $package) {
                            /** @var Package $package */

                            $output[$package->getName()] = $package->getVersion();
                        }

                        $type = array('name' => 'array');

                        return $visitor->visitArray($output, $type, $context);
                    }
                );
            })
            ->configureHandlers(function(HandlerRegistry $registry) {
                $registry->registerHandler(
                    'deserialization',
                    'App\Satis\Collections\RepositoryCollection',
                    'json',
                    function(VisitorInterface $visitor, array $data, array $type, Context $context) {
                        /** @var \App\Satis\Model\Repository[] $repositories */
                        $repositories = $visitor->visitArray($data, $type, $context);

                        $collection = new RepositoryCollection();
                        foreach($repositories as $repository) {
                            $collection->put($repository->getId(), $repository);
                        }

                        return $collection;
                    }
                );

                $registry->registerHandler(
                    'deserialization',
                    'App\Satis\Collections\PackageCollection',
                    'json',
                    function(VisitorInterface $visitor, array $data, array $type, Context $context) {
                        $temp = [];
                        foreach($data as $name => $version) {
                            $temp[] = ['name' => $name, 'version' => $version];
                        }

                        /** @var \App\Satis\Model\Package[] $packages */
                        $packages = $visitor->visitArray($temp, $type, $context);

                        $collection = new PackageCollection();
                        foreach($packages as $package) {
                            $collection->put($package->getId(), $package);
                        }

                        return $collection;
                    }
                );
            })
            ->build();

        $this->app->bind('App\Satis\ConfigMirror', function($app) use($serializer) {
            return new ConfigMirror($serializer);
        });

        $this->app->bind('App\Satis\ConfigManager', function($app) use($serializer) {
            $configPersister = $this->app->make('App\Satis\ConfigPersister');

            return new ConfigManager($configPersister, new ConfigBuilder($configPersister), $serializer);
        });

        $this->app->bind('App\Satis\ConfigPersister', function($app) use($serializer) {
            $filesystem = new Filesystem();
            $configLock = new ConfigLock();
            $configMirror = $this->app->make('App\Satis\ConfigMirror');

            return new ConfigPersister($filesystem, $configLock, $configMirror, $serializer);
        });

        $this->app->bind('JMS\Serializer\Serializer', function($app) use($serializer) {
            return $serializer;
        });
    }
}
