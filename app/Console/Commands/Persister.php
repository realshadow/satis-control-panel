<?php

namespace App\Console\Commands;

use App\Satis\ConfigPersister;
use Illuminate\Console\Command;

class Persister extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'satis:persister:unlock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Forcefully unlocks UI.';

    /** @var ConfigPersister $configPersister */
    protected $configPersister;

    /**
     * Create a new command instance.
     *
     * @param \App\Satis\ConfigPersister $configPersister
     * @internal param \Illuminate\Filesystem\Filesystem $filesystem
     */
    public function __construct(ConfigPersister $configPersister) {
        parent::__construct();

        $this->configPersister = $configPersister;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle() {
        $this->info('Unlocking UI.');

        $this->configPersister->unlock();
    }
}
