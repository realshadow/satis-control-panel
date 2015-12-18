<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class Permissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'satis:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets permissions required for running UI and builder.';

    /** @var Filesystem $filesystem */
    protected $filesystem;

    /**
     * Create a new command instance.
     *
     * @param \Illuminate\Filesystem\Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem) {
        parent::__construct();

        $this->filesystem = $filesystem;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle() {
        $this->info('Settings permissions for satis config file.');

        $configFile = config('satis.config');

        if($this->filesystem->exists($configFile)) {
            chmod($configFile, 777);
        } else {
            $this->error("\n" . 'Satis config file "' . $configFile . '" does not exist.
                Did you forget to create it?');
        }
    }
}
