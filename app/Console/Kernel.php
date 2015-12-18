<?php

namespace App\Console;

use App\Satis\ConfigManager;
use App\Satis\Context\PublicRepository;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\Inspire::class,
        \App\Console\Commands\Permissions::class,
        \App\Console\Commands\Persister::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        $schedule->call(function() {
            /** @var ConfigManager $configManager */
            $configManager = $this->app->make('App\Satis\ConfigManager');

            $buildContext = new PublicRepository();

            $configManager->forceBuild($buildContext);
        })->everyMinute()
            ->name('satis-config')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/cron.log'));
    }
}
