<?php

namespace App\Console\Commands\UCCX;

use App\Uccx;
use App\TelecomInfrastructure;
use Carbon\Carbon;
use Mail;


use Illuminate\Console\Command;

class UccxTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uccx:getFinesseSystemInfo';

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
		print $start.PHP_EOL;
		
		$server = env('UCCXCONNECTION_URL');
		
		$servers = TelecomInfrastructure::where('application',"Unified CCX")->get();
		
		print_r($servers);
		
		
		foreach($servers as $server){
			$url = $server['mgmt_url'];
			$response = Uccx::getFinesseSystemInfo($url);
			
			if(!$response){
				print "Did not get a resonse".PHP_EOL;
			}else{
				if(isset($response['response']) && $response['response']){
					$status = $response['response']['status']; 
					$data = $response['response'];
					$jsonData = json_encode($response['response'], JSON_PRETTY_PRINT);
					//$jsonHTML = str_replace("\n", "<br>", $jsonData);
					$data['url'] = $url;
					$data['response'] = $jsonData;
					if($status != "IN_SERVICE"){
						$message = "The UCCX finesse service is not inservice!!!";
						print $message.PHP_EOL; 
						$this->sendemail($data);
					}else{
						$data['message'] = "";
						print "Its working".PHP_EOL;
						//$this->sendemail($data);
					}
				}
			}
		}
    }
	
	public function sendemail($data)
    {
        // Send email to the Oncall threshold met.

        // The HTML View is in resources/views/uccxmonioralarm.blade.php
        Mail::send(['html'=>'uccxmonioralarm'], $data, function ($message) {
            $message->subject('Telecom Management Alert - UCCX Finesse Alert!')
                        //->from([env('MAIL_FROM_ADDRESS')])
                        ->to([env('ONCALL_EMAIL_TO')])
                        ->bcc([env('BACKUP_EMAIL_TO')]);
        });

        echo 'Email sent to '.env('ONCALL_EMAIL_TO').PHP_EOL;
        echo 'Email sent to '.env('BACKUP_EMAIL_TO').PHP_EOL;
    }
}
