<?php

namespace App\Console\Commands\UCCX;

use App\TelecomInfrastructure;
use App\UccxFinesseAgent;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Mail;

class UccxAgentLoginMonitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uccx:agent-login-monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get System Info Status from UCCX';

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
        echo $start.PHP_EOL;

		$userid = env('UCCX_FINESSE_USER');

        $servers = TelecomInfrastructure::where('application', 'Unified CCX')->get();

        //print_r($servers);

        foreach ($servers as $server) {
            $url = $server['mgmt_url'];
			print "Get user on {$url}".PHP_EOL;
			
			
            $response = UccxFinesseAgent::getUser($url,$userid);
			//print_r($response); 
			
			$state = $response['response']['state']; 
			print "{$userid} state is {$state}".PHP_EOL;
			
			$extension = env('UCCX_FINESSE_EXTENSION');
			
			print "Login user {$userid} on {$url} using extention {$extension}".PHP_EOL;
			$login_response = UccxFinesseAgent::userLogin($url,$userid,$extension);
			print_r($login_response);

			sleep(5);
			$response = UccxFinesseAgent::getUser($url,$userid);
			if(isset($response['response']['state'])){
				$state = $response['response']['state']; 
				print "{$userid} state is {$state}".PHP_EOL;
				//print_r($response); 
			}else{
				$state = null;
			}

			$data = $response['response'];
			$jsonData = json_encode($response['response'], JSON_PRETTY_PRINT);
			//$jsonHTML = str_replace("\n", "<br>", $jsonData);
			$data['url'] = $url;
			$login_response = json_encode($login_response, JSON_PRETTY_PRINT);
			$data['login_response'] = $login_response; 
			$data['state'] = $state;
			$data['response'] = $jsonData;
			//print_r($data);
			if($state != "NOT_READY"){
				$this->sendemail($data);
			}
			

			print "Logout user {$userid} on {$url}".PHP_EOL;
			$response = UccxFinesseAgent::userLogout($url,$userid,$extension);
			//print_r($response); 
			
			sleep(5);
			print "Get user {$userid} on {$url}".PHP_EOL;
			$response = UccxFinesseAgent::getUser($url,$userid);
			$state = $response['response']['state'].PHP_EOL; 
			print "{$userid} state is {$state}".PHP_EOL;
			//print_r($response); 
			
			
			/*
            if (! $response) {
                echo 'Did not get a resonse'.PHP_EOL;
            } else {
                if (isset($response['response']) && $response['response']) {
                    $status = $response['response']['status'];
                    $data = $response['response'];
                    $jsonData = json_encode($response['response'], JSON_PRETTY_PRINT);
                    //$jsonHTML = str_replace("\n", "<br>", $jsonData);
                    $data['url'] = $url;
                    $data['response'] = $jsonData;
                    if ($status != 'IN_SERVICE') {
                        $message = 'The UCCX finesse service is not inservice!!!';
                        echo $message.PHP_EOL;
                        $this->sendemail($data);
                    } else {
                        $data['message'] = '';
                        echo 'Its working'.PHP_EOL;
                        //$this->sendemail($data);
                    }
                }
            }
			
			*/
        }
    }

    public function sendemail($data)
    {
        // Send email to the Oncall threshold met.

        // The HTML View is in resources/views/uccxfinessemonitoralarm.blade.php
        Mail::send(['html'=>'uccxfinessemonitoralarm'], $data, function ($message) {
            $message->subject('Telecom Management Alert - UCCX Finesse Alert!')
                        //->from([env('MAIL_FROM_ADDRESS')])
                        //->to([env('ONCALL_EMAIL_TO')])
                        ->bcc([env('BACKUP_EMAIL_TO')]);
        });

        echo 'Email sent to '.env('ONCALL_EMAIL_TO').PHP_EOL;
        echo 'Email sent to '.env('BACKUP_EMAIL_TO').PHP_EOL;
    }
}
