<?php

namespace App\Satis;

use App\Satis\Exceptions\PackageBuildFailedException;

/**
 * @author Lukas Homza <lukashomz@gmail.com>
 */
class ConfigBuilder {
    const PRIVATE_REPOSITORY = 1;
    const PUBLIC_REPOSITORY = 2;

    /** @var \App\Satis\ConfigPersister $configPersister */
    protected $configPersister;

    /** @var BuildContext $buildContext */
    protected $buildContext;

    /** @var bool $asyncMode */
    protected $asyncMode = true;

	/**
     * ConfigBuilder constructor.
     * @param \App\Satis\ConfigPersister $configPersister
     */
    public function __construct(ConfigPersister $configPersister) {
        $this->configPersister = $configPersister;
    }

    /**
     * @param BuildContext $buildContext
     * @return ConfigBuilder
     */
    public function setBuildContext(BuildContext $buildContext) {
        $this->buildContext = $buildContext;

        return $this;
    }

    /**
     * @param boolean $asyncMode
     * @return ConfigManager
     */
    public function setAsyncMode($asyncMode) {
        $this->asyncMode = $asyncMode;

        return $this;
    }

	/**
     * @return bool|null
     * @throws \App\Satis\Exceptions\PackageBuildFailedException
     */
    public function build() {
        $this->configPersister->lock($this->buildContext->getItemId());

        $command = new BuildCommand(
            $this->buildContext->getConfigFile(),
            config('satis.build_directory') . DIRECTORY_SEPARATOR . $this->buildContext->getBuildDirectory(),
            config('satis.proxy')
        );

        $command->setItem($this->buildContext->getItemName());

        $output = null;
        try {
            $output = $command->withCd(base_path())->run($this->asyncMode);
        } catch(PackageBuildFailedException $e) {
            $output = $e->getMessage();
        } finally {
            if($this->asyncMode === false) {
                $this->configPersister->unlock($this->buildContext->getItemId());
            }
        }

        return $output;
    }
}
