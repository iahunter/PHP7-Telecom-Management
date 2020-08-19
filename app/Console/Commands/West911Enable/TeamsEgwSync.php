<?php

namespace App\Console\Commands\West911Enable;

use App\Did;
use App\West911EnableEGW;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TeamsEgwSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'west911enable:teams-egw-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Teams enabled Voice Users and Update sync EGW with Phone Number in use';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    private $teamspbxname;

    public function __construct()
    {
        $this->teamspbxname = env('TEAMS_EGW_PBX_NAME');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $starttime = Carbon::now();
        echo 'Starting - '.$starttime.PHP_EOL;

        $teams = Did::where('system_id', 'like', '%MicrosoftTeams%')
                ->where('country_code', '1')
                ->get();

        $users = [];
        foreach ($teams as $user) {
            //print $user->number.PHP_EOL;
            //print_r($user->assignments['MicrosoftTeams']).PHP_EOL;
            $users[$user->number] = $user->assignments['MicrosoftTeams'];
        }

        print_r($users);

        $endpoints = West911EnableEGW::get_all_endpoints();

        //print_r($endpoints);

        $extensions = [];
        $deletes = [];
        foreach ($endpoints as $endpoint) {
            //print_r($endpoint);
            if ($endpoint->extension && $endpoint->ip_pbx_name == $this->teamspbxname) {
                $extensions[$endpoint->endpoint_id] = $endpoint->extension;

                if (! isset($users[$endpoint->extension])) {
                    echo "{$endpoint->extension} needs removed from the EGW...".PHP_EOL;
                    $deletes[] = $endpoint->extension;
                }
            }
        }

        print_r($extensions);

        $adds = [];
        foreach ($users as $number => $user_array) {
            if (! in_array($number, $extensions)) {
                echo "Need to add {$number} to the EGW".PHP_EOL;
                $adds[] = $number;
            }
        }

        $count = count($adds);
        echo "Found {$count} Endpoints to Add".PHP_EOL;
        print_r($adds);
        $this->add_extensions_to_egw($adds);

        $count = count($deletes);
        echo "Found {$count} Endpoints to Delete".PHP_EOL;
        print_r($deletes);
        $this->delete_extensions_from_egw($deletes);
    }

    private function add_extensions_to_egw($extensions)
    {
        $egwApiClient = new \EmergencyGateway\EGW(
                                                    'https://'.env('EGW_HOST').env('E911_ENDPOINT_SOAP_URL'),
                                                    'https://'.env('EGW_HOST').env('E911_ENDPOINT_SOAP_WSDL'),
                                                    env('E911_SOAP_USER'),
                                                    env('E911_SOAP_PASS'),
                                                    env('E911_SNMP_RW')
                                            );

        foreach ($extensions as $extension) {
            echo "Adding Extension: {$extension}...".PHP_EOL;

            $array = [
                'ip_pbx_name' 	=> $this->teamspbxname,
                'endpoint'		   => $extension,
            ];

            try {
                $RESULT = $egwApiClient->add_endpoint($array);
            } catch (\Exception $e) {
                \Log::info('egw_add_endpoint', ['data' => "Adding {$extension} to EGW failed with exception: {$e->getMessage()}"]);
            }

            echo "Adding Extension: {$extension} Complete".PHP_EOL;
        }
    }

    private function delete_extensions_from_egw($extensions)
    {
        $egwApiClient = new \EmergencyGateway\EGW(
                                                    'https://'.env('EGW_HOST').env('E911_ENDPOINT_SOAP_URL'),
                                                    'https://'.env('EGW_HOST').env('E911_ENDPOINT_SOAP_WSDL'),
                                                    env('E911_SOAP_USER'),
                                                    env('E911_SOAP_PASS'),
                                                    env('E911_SNMP_RW')
                                            );

        foreach ($extensions as $extension) {
            echo "Deleting Extension: {$extension}...".PHP_EOL;

            $array = [
                'ip_pbx_name' 	=> $this->teamspbxname,
                'endpoint'		   => $extension,
            ];

            try {
                $RESULT = $egwApiClient->delete_endpoint($array);
            } catch (\Exception $e) {
                \Log::info('egw_delete_endpoint', ['data' => "Deleting {$extension} to EGW failed with exception: {$e->getMessage()}"]);
            }

            echo "Deleting Extension: {$extension} Complete".PHP_EOL;
        }
    }
}
