<?php

namespace App\Satis;

use App\Satis\Context\AsyncCommand;
use App\Satis\Context\SyncCommand;
use App\Satis\Exceptions\PackageBuildFailedException;
use App\Satis\Model\Repository;
use Illuminate\Support\Collection;
use Monolog\Logger;

/**
 * @author Lukas Homza <lukashomz@gmail.com>
 */
class BuildCommand {
    /** @var BuildContext $buildContext */
    protected $buildContext;

    /** @var string $executable */
    protected $executable = 'bin%ssatis';

    /** @var string $command */
    protected $command = 'build';

    /** @var string $configPath */
    protected $configPath;

    /** @var string $configPath */
    protected $buildDirectory;

    /** @var \Illuminate\Support\Collection $proxySettings */
    protected $proxySettings;

    /** @var string $item */
    protected $item;

    /** @var string $directory */
    protected $directory;

    /** @var string $currentDirectory */
    protected $currentDirectory;

    /** @var \Monolog\Logger $logger */
    protected $logger;

    /** @var string $logFile */
    protected $logFile;

    /** @var array $commandOutput */
    protected $commandOutput = [];

    /**
     * @param CommandContextInterface $commandContext
     * @return Collection
     */
    protected function compile(CommandContextInterface $commandContext) {
        $this->logFile = storage_path('logs/async/' . (string) round(microtime(true) * 1000) .
            mt_rand(1, 10000) . '.log');

        $memoryLimit = config('satis.memory_limit');
        $buildVerbosity = config('satis.build_verbosity');

        $chunks = new Collection([
            'php' . ($memoryLimit !== null ? ' -dmemory_limit=' . $memoryLimit : ''),
            sprintf($this->executable, DIRECTORY_SEPARATOR),
            $this->command . ($buildVerbosity !== null ? ' -' . $buildVerbosity : ''),
            $this->configPath,
            $this->buildDirectory
        ]);

        if($this->item !== null) {
            $chunks->push($this->item);
        }

        $chunks->push($commandContext->getOutputRedirection($this->logFile));
        $chunks->push($commandContext->getShouldUnlockOnCompletion());

        foreach(['http', 'https'] as $protocol) {
            $proxy = $this->proxySettings->get($protocol);
            if($proxy !== null) {
                $chunks->prepend(strtoupper($protocol) . '_PROXY=' . $proxy);
            }
        }

        $chunks->reject(function($commandChunk) {
            return trim($commandChunk) === '';
        });

        return $chunks;
    }

    /**
     * @return \Monolog\Logger
     */
    protected function getLogger() {
        return \Log::getMonolog();
    }

    /**
     * @return bool
     */
    protected function isWindows() {
        return PHP_OS === 'WINNT' || PHP_OS === 'WIN32';
    }

    /**
     * @param CommandContextInterface $commandContext
     * @return mixed
     */
    protected function exec(CommandContextInterface $commandContext) {
        $commandChunks = $this->compile($commandContext);
        $logger = $commandContext->getLogger();

        $logger->info(str_repeat('=', 30));
        $logger->info('Running command => ' . PHP_EOL . $commandChunks->implode(' '));

        chdir($this->directory);

        exec($commandChunks->implode(' '), $output, $result);

        chdir($this->currentDirectory);

        if($commandContext instanceof SyncCommand) {
            $logger->info('Command output => ' . implode(PHP_EOL, $output));
        } else {
            $logger->notice('Command output can be found in "' . $this->logFile . '".');
        }

        $this->setCommandOutput($output);

        $logger->info(str_repeat('=', 30));

        return $result;
    }

    /**
     * @param mixed $commandOutput
     * @return BuildCommand
     */
    protected function setCommandOutput($commandOutput) {
        $this->commandOutput = $commandOutput;

        return $this;
    }

    /**
     * BuildCommand constructor.
     * @param string $configPath
     * @param string $buildDirectory
     * @param array $proxySettings
     */
    public function __construct($configPath, $buildDirectory, array $proxySettings = array()) {
        $this->configPath = escapeshellarg($configPath);
        $this->buildDirectory = escapeshellarg($buildDirectory);
        $this->proxySettings = new Collection($proxySettings);
    }

    /**
     * @param \App\Satis\BuildContext $buildContext
     * @return $this
     */
    public function setContext(BuildContext $buildContext) {
        $this->buildContext = $buildContext;

        return $this;
    }

    /**
     * @param string $item
     */
    public function setItem($item) {
        if($item === null) {
            return;
        }

        if(preg_match(Repository::REGEX, $item)) {
            $this->item = '--repository-url ' . escapeshellarg($item);
        } else {
            $this->item = escapeshellarg($item);
        }
    }

    /**
     * @param \Monolog\Logger $logger
     * @return BuildCommand
     */
    public function setLogger(Logger $logger) {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param string $directory
     * @return BuildCommand
     */
    public function withCd($directory) {
        $this->directory = $directory;
        $this->currentDirectory = getcwd();

        return $this;
    }

    /**
     * @param bool $asyncMode
     * @return bool
     * @throws PackageBuildFailedException
     */
    public function run($asyncMode = true) {
        # -- force sync
        #if(true) {
        if($asyncMode === false || $this->isWindows() === true) {
            if($this->isWindows() === true) {
                $this->getLogger()
                    ->warn('OS does not support async mode, forcing sync.');
            }

            return $this->runSync();
        }

        $this->exec(new AsyncCommand());

        return true;
    }

    /**
     * @return bool
     * @throws PackageBuildFailedException
     */
    public function runSync() {
        set_time_limit(config('satis.sync_timeout'));

        $result = $this->exec(new SyncCommand());

        if($result !== 0) {
            throw new PackageBuildFailedException('Package build failed. Check build log for details.');
        }

        return $this->commandOutput;
    }
}
