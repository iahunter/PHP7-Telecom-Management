<?php

namespace App;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Cookie\FileCookieJar as FileCookieJar;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Eloquent\Model;

class Uccx extends Model
{
    // Guzzle API Wrapper for Unity Connection CUPI
    public static function wrapapi($verb, $apiurl, $query = '', $json = '')
    {
        // Wrapper for Guzzle API Calls
        $client = new GuzzleHttpClient();

        $headers = [
            'auth'    => [env('UCCXCONNECTION_USER'), env('UCCXCONNECTION_PASS')],
            'verify'  => false,
            'headers' => [
                //'Content-Type'     => 'application/xml',
                'Accept'           => 'application/xml',
            ],
            //'debug' => true,
            //'http_errors' => true,
        ];
        if ($query != '') {
            $headers['query'] = $query;
        }
        if ($json != '') {
            $headers['json'] = $json;
        }

        $response = [];

        try {
            $apiRequest = $client->request($verb, $apiurl, $headers);
			
			//print_r($apiurl);

            $response['status_code'] = $apiRequest->getStatusCode();
			
			$xml = $apiRequest->getBody()->getContents();
			
			$xml = simplexml_load_string($xml);
			$result = json_encode($xml);
			
			print_r($result);

            $result = json_decode($result, true);
			
			print_r($result);

            $response = [
                'success'        => true,
                'message'        => '',
                'response'       => $result,
            ];
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
            $response['success'] = false;
            $response['response'] = $e->getMessage();
        }

        return $response;

        //return json_decode($apiRequest->getBody()->getContents(), true);
    }

    public static function getFinesseSystemInfo($server)
    {
        $verb = 'GET';
		$apiurl = "{$server}:8445/finesse/api/SystemInfo";
        $query = "";
        $json = '';

        return self::wrapapi($verb, $apiurl, $query);
    }


    public static function createuser($username, $dn, $template)
    {
        $verb = 'POST';
        $apiurl = '/users';
        $query = ['templateAlias' => $template];
        $json = [
            'Alias'           => $username,
            'DtmfAccessId'    => $dn,
        ];

        $import = self::wrapapi($verb, $apiurl, $query, $json);

        $user = self::finduserbyalias($username);

        $return = ['return' => $import, 'user' => $user];

        return $return;
    }

    public static function deleteuser($username)
    {

        //$OVERRIDE = "true";
        $userarray = [];
        $userarray['username'] = $username;

        // Check if user has a current mailbox.
        $mailbox = self::finduserbyalias($username);
        $mailbox = $mailbox['response'];

        if (isset($mailbox['@total']) && $mailbox['@total'] == 0) {
            abort(404, 'User not found');
        }

        //return $mailbox;
        $objectid = '';

        if (isset($mailbox['User']['ObjectId'])) {
            $objectid = $mailbox['User']['ObjectId'];
        }

        $verb = 'DELETE';
        $apiurl = "/users/{$objectid}";
        $query = '';
        $json = '';

        $import = self::wrapapi($verb, $apiurl, $query, $json);

        $return = ['return' => $import, 'deleted' => $username];

        return $return;
    }

    public static function updateUserbyobjectid($ID, $UPDATE = [])
    {
        $verb = 'PUT';
        $apiurl = "/users/{$ID}";
        $query = '';
        $json = $UPDATE;

        $import = self::wrapapi($verb, $apiurl, $query, $json);

        $return = ['return' => $import];

        return $return;
    }

}
