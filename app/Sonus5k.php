<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Cookie\FileCookieJar as FileCookieJar;

class Sonus5k extends Model
{
    // Sonus 5K REST API Functions

	
    public static function listactivecalls()
    {
		$headers = [
							'auth'    => [env('SONUSUSER'), env('SONUSPASS')],
							'verify'  => false,
							'headers' => [
										'Content-Type'     => 'application/vnd.yang.data+json',
										'Accept'           => 'application/vnd.yang.data+json',
									]
						];
		
        $client = new GuzzleHttpClient();
		
		$SBCS = [
					env('SONUS1_URL'), 
					env('SONUS2_URL'), 
		]; 

		$CALLS = [];
		foreach($SBCS as $SBC){
			
			$URL = $SBC;
			
			$apiRequest = $client->request('GET', "{$URL}/api/operational/global/callSummaryStatus/", $headers);
			
			$CALLS[$SBC]= json_decode($apiRequest->getBody()->getContents(), true);
		}

        return $CALLS;
    }
}
