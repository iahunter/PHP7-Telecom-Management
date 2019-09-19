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

        Commands\CallManager\Ldapsync::class,
        Commands\CallManager\CucmSiteScan::class,
        Commands\CallManager\CucmPhoneScan::class,
        Commands\CallManager\DidScanCucm::class,

        Commands\CallManager\CucmPhoneScanOnDemand::class,
        Commands\CallManager\CucmPhoneSiteMove::class,

        Commands\CallManager\CucmPhoneIPAddresses::class,
        Commands\CallManager\CucmGatewayCallCounts::class,
        Commands\CallManager\CucmSIPPhoneReport::class,
        Commands\CallManager\CucmPhoneNamesCache::class,
        //
        // Cleanup
        Commands\CallManager\CucmPhoneandNumberCleanup::class,
        Commands\CallManager\CucmNumberCleanup::class,

        // CDRs
        Commands\CallManager\GetCucmCDRs::class,
        Commands\CallManager\CleanupOldCucmCDRSInDB::class,

        // Phone
        Commands\CallManager\CiscoWirelessPhoneCert::class,

        Commands\MACD\GetMacdReports::class,

        //Commands\CallManager\ADNumberUpdatesByMailboxNumber::class,

        Commands\UnityConnection\DidNumberUpdatesByMailboxNumber::class,
        Commands\West911Enable\PhoneEGWScanUpdate::class,

        Commands\Sonus\SonusCalls::class,
        Commands\Sonus\GetSonusConfig::class,
        Commands\Sonus\GetSonusCallReports::class,
        Commands\Sonus\SonusActiveCallsCache::class,
        Commands\Sonus\CleanSonusLogs::class,

        // CDRs
        Commands\Sonus\GetSonusCDRs::class,
        Commands\Sonus\CleanupOldSonusCDRSInDB::class,
        Commands\Sonus\CleanupLoopCDRS::class,

        Commands\OnCall\OnCallPermissions::class,

        Commands\BouncerPermissions\BouncerPermissions::class,
        Commands\BouncerPermissions\ReviewGroupPermissions::class,

        Commands\Monitoring\PingScanInfrastructure::class,
        Commands\Monitoring\SonusAlarmMonitor::class,
        Commands\Monitoring\SonusAttemptMonitor::class,
        Commands\Monitoring\CucmSonusLoopMitigator::class,
        Commands\Monitoring\MACDJobMonitor::class,

        Commands\Numbers\NumberSearch::class,

        // IDM
        Commands\IDM\IdmUpdateUserPhone::class,

        // Commented out one time scripts - Uncomment if needed.
        //Commands\CallManager\SiteLocal911::class,
        //Commands\CallManager\SiteDetailsReport::class,
        //Commands\CallManager\CssReport::class,
        //Commands\CallManager\OwnerUpdate::class,
        //Commands\CallManager\BuildCUCMSiteDefaults::class,

        //Commands\CallManager\AddPhones::class,
        //Commands\CallManager\LdapUserUpdate::class,

        //Commands\UnityConnection\UnityConnMB::class,
        Commands\UnityConnection\UnityMBNumberPopulateIDM::class,

        Commands\Ldap\TestLdapPhoneUpdate::class,

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
