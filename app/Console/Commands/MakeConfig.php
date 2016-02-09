<?php

namespace App\Console\Commands;

use App\Satis\ConfigManager;
use Illuminate\Console\Command;

class MakeConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'satis:make:config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mannualy creates config mirrors.';

    /** @var ConfigManager $configManager */
    protected $configManager;

    /** @var ConfigPersister $configPersister */
    protected $configPersister;

    /**
     * Create a new command instance.
     *
     * @param ConfigManager $configManager
     * @internal param \Illuminate\Filesystem\Filesystem $filesystem
     */
    public function __construct(ConfigManager $configManager) {
        parent::__construct();

        $this->configManager = $configManager;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle() {
        $this->info('Creating mirrored configuration files.');

        $this->configManager
            ->setDisableBuild(true)
            ->save();
    }
}
