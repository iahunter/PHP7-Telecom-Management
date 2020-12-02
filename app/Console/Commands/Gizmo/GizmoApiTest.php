<?php

namespace App\Console\Commands\Gizmo;

use App\Gizmo\RestApiClient as Gizmo;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GizmoApiTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gizmo:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Gizmo API';

    private $client;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
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
        echo 'Starting: '.$start.PHP_EOL;

        $this->client = new Gizmo(env('MICROSOFT_TENANT'), env('GIZMO_URL'), env('GIZMO_CLIENT_ID'), env('GIZMO_CLIENT_SECRET'), env('GIZMO_SCOPE'));

        $this->client->get_oauth2_token();

        $COUNTRYCODE = '1';
        $NPANXX = '402555';

        //$this->test_get_teams_numbers_by_NPA_NXX($COUNTRYCODE, $NPANXX);

        //$this->test_civic_address();

        //$return = $this->client->get_teams_csonline_users_voice_enabled();

        /*

        $userid = "John.Doe";
        $number = "14025551234";


        $user = $this->test_teams_user($userid, $number);

        print_r($user);


        $user = $this->client->get_teams_csonline_user_by_userid($userid);

        print_r($user);
        */

        //$user = $this->client->get_teams_csonline_user_by_sip_address('test@domain.com');

        //print_r($user);

        $numbers = $this->client->get_teams_csonlineappinstance_autoattendant();

        print_r($numbers);

        echo 'Started: '.$start.PHP_EOL;
        $end = Carbon::now();
        echo 'Stopped: '.$end.PHP_EOL;
    }

    protected function test_get_teams_numbers_by_NPA_NXX($COUNTRYCODE, $NPANXX)
    {

        //Testing NPANXX Usages

        $NPANXX = "{$COUNTRYCODE}{$NPANXX}";

        $users = $this->client->get_teams_csonline_all_users_by_NPA_NXX($NPANXX);

        $total = count($users);
        $count = 0;
        print_r($users);

        echo "Found {$total} Users".PHP_EOL;

        return $users;
    }

    protected function test_teams_user($userid, $number)
    {
        $domain = env('DOMAIN');
        // Test Civic Address JSON
        $json = <<<EOT
{
	"Alias":"{$userid}",
	"SipAddress":"sip:{$userid}@{$domain}",
    "OnPremLineURI":"TEL:+{$number}",
    "EnterpriseVoiceEnabled":"True",
    "HostedVoiceMail":"True"
}
EOT;

        print_r($json);

        $return = $this->client->set_teams_user($json);

        print_r($return);
    }

    protected function test_civic_address()
    {

        // Test Civic Address JSON
        $json = <<<'EOT'
{
	"companyName": "TEST1234",
	"houseNumber": "12345",
	"streetName": "Main",
	"streetSuffix": "St",
	"city": "Omaha",
	"state": "NE",
	"countryOrRegion": "US",
	"postalCode": "68164",
	"description": "TEST1234",
	"elin": "TEAMS_TRAVIS01"
}
EOT;

        $id = $this->client->create_civic_address($json);

        echo $id.PHP_EOL;

        echo "Deleting Civic Address ID: {$id}".PHP_EOL;

        $return = $this->client->delete_civic_address_by_id($id);

        echo $return;
    }
}
