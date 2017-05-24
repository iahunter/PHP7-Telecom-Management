<?php

namespace App\Console;

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
        Commands\Inspire::class,
        Commands\DidScan\Callmanager::class,
        Commands\CallManager\Ldapsync::class,
        Commands\CallManager\CucmSiteScan::class,
        Commands\CallManager\CucmPhoneScan::class,
        Commands\Sonus\SonusCalls::class,
        Commands\Sonus\GetSonusConfig::class,
        Commands\Sonus\GetSonusCDRs::class,
        Commands\OnCall\OnCallPermissions::class,
        Commands\BouncerPermissions\BouncerPermissions::class,
		Commands\Monitoring\PingScanInfrastructure::class,

        // Commented out one time scripts - Uncomment if needed.
        //Commands\CallManager\SiteLocal911::class,
        //Commands\CallManager\SiteDetailsReport::class,
        //Commands\CallManager\CssReport::class,
        //Commands\CallManager\OwnerUpdate::class,
        //Commands\CallManager\BuildCUCMSiteDefaults::class,

        //Commands\CallManager\AddPhones::class,
        //Commands\CallManager\LdapUserUpdate::class,

        //Commands\UnityConnection\UnityConnMB::class,

    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
