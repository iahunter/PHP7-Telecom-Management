<?php

namespace App;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Cookie\FileCookieJar as FileCookieJar;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Illuminate\Database\Eloquent\Model;

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
                'Accept'           => 'application/vnd.yang.data+xml',			// Changed to xml because Sonus is not supporting JSON - 042118 - TR
                //'Accept'           => 'application/vnd.yang.collection+xml',			// Changed to collection.xml for Sonus 7.2 upgrade. May look at json in future. 032120 - TR
            ],
        ];
        if ($verb == 'POST') {
            $headers['data'] = $data;
        }

        if (isset($data['Accept'])) {
            // Work around for Sonus stupidness... They aren't using the same type for all their API Calls. Have to manually change this for ones that changed in 5k 7.2.
            //print "Key Exists!!!".PHP_EOL;
            $headers['headers']['Accept'] = $data['Accept'];
        }

        try {
            $apiRequest = $client->request($verb, $apiurl, $headers);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        // Sonus is not supporting JSON at this time so we have to use XML - they are limiting the return on JSON to 100 records.
        $xml = $apiRequest->getBody()->getContents();

        // Try to convert the xml into an array.
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);

        return json_decode($json, true);

        //return json_decode($apiRequest->getBody()->getContents(), true);
    }

    public static function configbackup($SBC)
    {
        $verb = 'POST';
        $apiurl = "https://{$SBC}.".env('SONUS_DOMAIN_NAME')."/api/config/system/admin/{$SBC}/_operations/saveConfig";
        $result = self::wrapapi($verb, $apiurl);

        return $result;
    }

    public static function getactivecallstats($SBC)
    {
        $verb = 'GET';
        $apiurl = "https://{$SBC}.".env('SONUS_DOMAIN_NAME').'/api/operational/global/callCountStatus/activeCalls';

        $result = self::wrapapi($verb, $apiurl);

        //print_r($result);

        /*
        if(isset($result['key'])){
            return $result;
        }else{
            return null;
        }
        */

        return $result;
    }

    public static function listactivecalls($SBC)
    {
        $verb = 'GET';
        $apiurl = "https://{$SBC}.".env('SONUS_DOMAIN_NAME').'/api/operational/global/callSummaryStatus/';

        // Fixes for Sonus SBC Upgrade to 7.2 - not tested with 6. We came from version 5.0
        $sbcVersion = env('SONUS_VERSION');

        if ($sbcVersion && $sbcVersion > 7) {
            $data['Accept'] = 'application/vnd.yang.collection+xml';			// Changed to collection.xml for Sonus 7.2 upgrade. May look at json in future. 032120 - TR
        } else {
            $data = null;
        }

        $response = self::wrapapi($verb, $apiurl, $data);
        //print_r($response);
        // We just want to return an array of calls.
        if (isset($response['callSummaryStatus'])) {
            if (($response['callSummaryStatus']) && array_key_exists('GCID', $response['callSummaryStatus'])) {
                // Wrap the single object in an array - found GCID key inside CallSummaryStatus
                return [$response['callSummaryStatus']];
            } else {
                return $response['callSummaryStatus'];
            }
        } else {
            return;
        }

        //return self::wrapapi($verb, $apiurl);
    }

    public static function listactivealarms($SBC)
    {
        $verb = 'GET';
        $apiurl = "https://{$SBC}.".env('SONUS_DOMAIN_NAME').'/api/operational/alarms/currentStatus';

        // Fixes for Sonus SBC Upgrade to 7.2 - not tested with 6. We came from version 5.0
        $sbcVersion = env('SONUS_VERSION');

        if ($sbcVersion && $sbcVersion > 7) {
            $data['Accept'] = 'application/vnd.yang.collection+xml';			// Changed to collection.xml for Sonus 7.2 upgrade. May look at json in future. 032120 - TR
        } else {
            $data = null;
        }

        $response = self::wrapapi($verb, $apiurl, $data);
        //print_r($response);
        // We just want to return an array of alarms.
        if (isset($response['currentStatus'])) {
            if (($response['currentStatus']) && array_key_exists('alarmId', $response['currentStatus'])) {
                // Wrap the single object in an array - found GCID key inside CallSummaryStatus
                return [$response['currentStatus']];
            } else {
                return $response['currentStatus'];
            }
        } else {
            return;
        }

        //return self::wrapapi($verb, $apiurl);
    }

    public static function listcallDetailStatus($SBC)
    {
        $verb = 'GET';
        $apiurl = "https://{$SBC}.".env('SONUS_DOMAIN_NAME').'/api/operational/global/callDetailStatus';

        $response = self::wrapapi($verb, $apiurl);
        //return $response;

        // We just want to return an array of calls.
        if (($response['callDetailStatus']) && array_key_exists('GCID', $response['callDetailStatus'])) {
            // Wrap the single object in an array - found GCID key inside CallSummaryStatus
            return [$response['callDetailStatus']];
        } else {
            return $response['callDetailStatus'];
        }

        //return self::wrapapi($verb, $apiurl);
    }

    public static function listcallMediaStatus($SBC)
    {
        $verb = 'GET';
        $apiurl = "https://{$SBC}.".env('SONUS_DOMAIN_NAME').'/api/operational/global/callMediaStatus';

        // Example Call by GCID
        // $apiurl = https://{$SBC}/api/operational/global/callMediaStatus/34782

        //return self::wrapapi($verb, $apiurl);

        $response = self::wrapapi($verb, $apiurl);

        // We just want to return an array of calls.
        if (($response['callMediaStatus']) && array_key_exists('GCID', $response['callMediaStatus'])) {
            // Wrap the single object in an array - found GCID key inside CallSummaryStatus
            return [$response['callMediaStatus']];
        } else {
            return $response['callMediaStatus'];
        }
    }

    /*
    public static function removeconfigbackup($SBC, $LOCATION)
    {
        $LOCATION = ['data' => $LOCATION];
        $verb = "POST";
        $apiurl = "https://{$SBC}.".env('SONUS_DOMAIN_NAME')."/api/config/system/admin/{$SBC}/_operations/saveConfig";
        $result = self::wrapapi($verb, $apiurl, $LOCATION);
        print "REMOVED ".$LOCATION;
        print_r($result);
        return $result;
    }
    */
}
