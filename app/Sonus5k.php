<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Cookie\FileCookieJar as FileCookieJar;
use GuzzleHttp\Psr7;

class Sonus5k extends Model
{
    // Sonus 5K REST API Functions

    public static function wrapapi($verb, $apiurl, $data = '')
    {
        // Wrapper for Guzzle API Calls
        $client = new GuzzleHttpClient();

        $headers = [
                            'auth'    => [env('SONUSUSER'), env('SONUSPASS')],
                            'verify'  => false,
                            'headers' => [
                                        'Content-Type'     => 'application/vnd.yang.data+json',
                                        'Accept'           => 'application/vnd.yang.data+json',
                                    ],
                        ];
        if ($verb == 'POST') {
            $headers['data'] = $data;
        }
		
		try {
			$apiRequest = $client->request($verb, $apiurl, $headers);
		} catch (\Exception $e) {
				return $e->getMessage();
		}

        $apiRequest = $client->request($verb, $apiurl, $headers);

        return json_decode($apiRequest->getBody()->getContents(), true);
    }

    public static function configbackup($SBC)
    {
        $verb = 'POST';
        $apiurl = "https://{$SBC}/api/config/system/admin/{$SBC}/_operations/saveConfig";
        $result = self::wrapapi($verb, $apiurl);

        return $result;
    }

    /*
    public static function listactivecalls()
    {
        $SBCS = [
                    env('SONUS1'),
                    env('SONUS2'),
        ];

        $CALLS = [];
        foreach ($SBCS as $SBC) {
            $URL = $SBC;
            $verb = "GET";
            $apiurl = "https://{$SBC}/api/operational/global/callSummaryStatus/";
            $result = self::wrapapi($verb, $apiurl);
            $CALLS[$SBC] = $result ;
        }

        return $CALLS;
    }
    */

    public static function getactivecallstats($SBC)
    {
        $verb = 'GET';
        $apiurl = "https://{$SBC}/api/operational/global/callCountStatus/activeCalls";

        return self::wrapapi($verb, $apiurl);
    }

    public static function listactivecalls($SBC)
    {
        $verb = 'GET';
        $apiurl = "https://{$SBC}/api/operational/global/callSummaryStatus/";

        return self::wrapapi($verb, $apiurl);
    }

    public static function listactivealarms($SBC)
    {
        $verb = 'GET';
        $apiurl = "https://{$SBC}/api/operational/alarms/currentStatus";

        return self::wrapapi($verb, $apiurl);
    }

    public static function listcallDetailStatus($SBC)
    {
        $verb = 'GET';
        $apiurl = "https://{$SBC}/api/operational/global/callDetailStatus";

        return self::wrapapi($verb, $apiurl);
    }

    public static function listcallMediaStatus($SBC)
    {
        $verb = 'GET';
        $apiurl = "https://{$SBC}/api/operational/global/callMediaStatus";

        // Example Call by GCID
        // $apiurl = https://{$SBC}/api/operational/global/callMediaStatus/34782

        return self::wrapapi($verb, $apiurl);
    }

    /*
    public static function removeconfigbackup($SBC, $LOCATION)
    {
        $LOCATION = ['data' => $LOCATION];
        $verb = "POST";
        $apiurl = "https://{$SBC}/api/config/system/admin/{$SBC}/_operations/saveConfig";
        $result = self::wrapapi($verb, $apiurl, $LOCATION);
        print "REMOVED ".$LOCATION;
        print_r($result);
        return $result;
    }
    */
}
