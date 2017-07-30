<?php

namespace App\Console\Commands\CallManager;

use DB;
use Carbon\Carbon;
use App\Cucmsiteconfigs;
use App\Cucmphoneconfigs;
use Illuminate\Console\Command;

class CucmSiteScan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:sitescan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan CUCM Site Summary and Write to Database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Construct new cucm object
        $this->cucm = new \CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
                                                    storage_path(env('CALLMANAGER_WSDL')),
                                                    env('CALLMANAGER_USER'),
                                                    env('CALLMANAGER_PASS')
                                                    );

        $this->svn = env('CUCM_SVN');
        $this->extnlength = [];
        $this->shortext_exclude = ['KMXFDMDO'];

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $start = Carbon::now();
        echo 'Starting Site Scan at: '.$start.PHP_EOL;
        // Step 1. Get a list of sites by getting All the Device Pools.
        $sites = $this->getSites();                                    // Get a list of sites by calling get device pools and discard ones we don't care about.
        //$sites = ['TRAVIS01'];
        $dbsites = $this->getsitesfromdb();

        //print_r($dbsites);

        foreach ($dbsites as $dbsite) {
            if (! in_array($dbsite, $sites)) {
                //echo 'DELETING: '.$dbsite.PHP_EOL;
                print_r($this->deletesitecode($dbsite));
                echo 'DELETED: '.$dbsite.PHP_EOL;
            }
        }

        //$sites = ['ECDONP3G'];                                     	// Comment this out to actually run this.
        foreach ($sites as $site) {
            echo 'Getting Site: '.$site.PHP_EOL;
            // Step 2. Get everything to do with the site for each site.
            $site_summary = $this->getSiteDetails($site);
            //print_r($site_summary);

            $site_details = $this->getSiteOjbectDetails($site);
            //print_r($site_details);

            $devicepools = $site_details['DevicePool'];

            //print_r($devicepool);
            foreach ($devicepools as $devicepool) {
                $localRouteGroup = $devicepool['localRouteGroup']['value'];
                if ($localRouteGroup == 'RG_CENTRAL_SBC_GRP') {
                    $trunking = 'sip';
                } else {
                    $trunking = 'local';
                }
            }

            echo $trunking.PHP_EOL;

            $listcss = $site_details['Css'];
            $e911 = 'local';
            foreach ($listcss as $css) {
                foreach ($css['members']['member'] as $partition) {
                    //print_r($partition);
                    if (isset($partition['routePartitionName']['_'])) {
                        if ($partition['routePartitionName']['_'] == 'PT_911Enable') {
                            $e911 = '911enable';
                        }
                    }
                }
            }

            echo $e911.PHP_EOL;

            $this->create_update_site($site, $site_summary, $site_details, $e911, $trunking);
        }

        $end = Carbon::now();

        echo 'Start: '.$start;
        echo 'End: '.$end;
    }

    protected function getsitesfromdb()
    {
        $dbsites = DB::table('cucmsite')->where('deleted_at', '=', null)->select('sitecode')->orderBy('sitecode')->get();
        $dbsites = json_decode(json_encode($dbsites), true);
        //print_r($dbsites);
        $sites = [];
        foreach ($dbsites as $dbsite) {
            $sites[] = $dbsite['sitecode'];
        }

        return $sites;
    }

    protected function deletesitecode($sitecode)
    {
        if (file_exists("storage/cucm/{$this->svn}/sites/{$sitecode}")) {
            unlink("storage/cucm/{$this->svn}/sites/{$sitecode}");
        }
        echo 'ENTERED deletesitecode function';
        $record = Cucmsiteconfigs::where('sitecode', $sitecode)->first();
        //print_r($record);
        return $record->delete();                                                            // Delete the did block.
    }

    // This updates DID records with new information AND clears out no longer used phone numbers / sets them to available
    protected function create_update_site($sitecode, $site_summary, $site_details, $e911, $trunking)
    {
        // Reset extnlength
        $this->extnlength = [];

        $INSERT['sitecode'] = $sitecode;
        $INSERT['sitesummary'] = $site_summary;
        $INSERT['sitedetails'] = $site_details;
        $INSERT['e911'] = $e911;
        $INSERT['trunking'] = $trunking;
        $INSERT['shortextenlength'] = 4;

        $phonecount = Cucmphoneconfigs::where('devicepool', 'like', '%'.$sitecode.'%')->count();

        if (! in_array($sitecode, $this->shortext_exclude)) {
            echo 'Phone Count '.$phonecount.PHP_EOL;
            // Get extension Length from phone descriptions

            $phone_array = [];

            if ($phonecount) {
                $phone_array[] = Cucmphoneconfigs::where('devicepool', 'like', '%'.$sitecode.'%')->chunk(300, function ($phones) {
                    foreach ($phones as $phone) {
                        $desc_array = explode(' - ', $phone['description']);

                        if (is_array($desc_array)) {
                            $shortextn = array_pop($desc_array);
                            $shortextnlength = strlen($shortextn);
                            if (isset($this->extnlength[$shortextnlength])) {
                                $this->extnlength[$shortextnlength] = $this->extnlength[$shortextnlength] + 1;
                            } else {
                                $this->extnlength[$shortextnlength] = 1;
                            }
                        }
                    }
                });

                //print_r($this->extnlength);

                // Get the one with the most counts.
                $extnlength = array_keys($this->extnlength, max($this->extnlength));
                $extnlength = $extnlength[0];
                $INSERT['shortextenlength'] = $extnlength;
                echo 'Found Extension Length: '.$extnlength.PHP_EOL;
            } else {
                echo 'Did not Find Extension Length... Leaving Site at Default value of 4... '.PHP_EOL;
            }
        } else {
            echo 'Found Site in short extension excluded list. Leaving Site at Default value of 4...'.PHP_EOL;
        }

        // Save Site Config as JSON and upload to subversion for change tracking.
        $svn_save = json_encode($INSERT, JSON_PRETTY_PRINT);
        echo "Saving {$sitecode} json to file...".PHP_EOL;
        file_put_contents("storage/cucm/{$this->svn}/sites/{$sitecode}", $svn_save);
        //echo "Saved to file...".PHP_EOL;

        // Check if Site exists in the database
        if (Cucmsiteconfigs::where([['sitecode', $sitecode]])->count()) {
            $site = Cucmsiteconfigs::where([['sitecode', $sitecode]])->first();

            //print_r($site);
            echo 'Site Exists'.PHP_EOL;

            // Update Site with Current settings
            $site->sitesummary = $INSERT['sitesummary'];
            $site->sitedetails = $INSERT['sitedetails'];
            $site->e911 = $INSERT['e911'];
            $site->trunking = $INSERT['trunking'];
            $site->shortextenlength = $INSERT['shortextenlength'];

            echo 'Saving Site with current config...'.PHP_EOL;
            $site->save();
            echo 'Saved...'.PHP_EOL;
        } else {
            echo 'Creating Site: '.$sitecode.PHP_EOL;
            Cucmsiteconfigs::create($INSERT);
            echo 'Created Site: '.$sitecode.PHP_EOL;
        }
    }

    // Get a list of Sites by device pools.
    protected function getSites()
    {
        echo 'Getting sites from CUCM...'.PHP_EOL;
        try {
            $sites = $this->cucm->get_site_names();
            //$sites = ["TRAVIS01"];

            // Array of DP we don't want to include.
            $discard = ['TEST', 'Self_Provisioning', 'ECD', '911Enable', 'ATT_SIP', 'Travis', 'CENCOLIT', 'TEMPLATE', 'CENTRAL_SBC_SIPTRUNKS'];

            if (! $sites) {
                // Return blank array if no results in $didinfo.
                echo 'didinfo is blank!';

                return $sites;
            }
            foreach ($sites as $key => $site) {
                // Get rid of the crap we don't want.
                if (in_array($site, $discard)) {
                    echo 'Discarding: '.$site.PHP_EOL;
                    unset($sites[$key]);
                }
            }

            if (! count($sites)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }

            return $sites;
        } catch (\Exception $e) {
            echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }
    }

     // Get a summary of all types supported by site.
     protected function getSiteDetails($SITE)
     {
         try {
             $site_details = $this->cucm->get_all_object_types_by_site($SITE);
         } catch (\Exception $e) {
             echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
         }

         return $site_details;
     }

     // Get a summary of all types supported by site.
     protected function getSiteOjbectDetails($SITE)
     {
         try {
             $site_object_details = $this->cucm->get_all_object_type_details_by_site($SITE);
         } catch (\Exception $e) {
             echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
         }

         return $site_object_details;
     }
}
