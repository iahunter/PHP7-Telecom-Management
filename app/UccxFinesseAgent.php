<?php

namespace App;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Cookie\FileCookieJar as FileCookieJar;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Eloquent\Model;

class UccxFinesseAgent extends Model
{
    // Guzzle API Wrapper for Unity Connection CUPI
    public static function wrapapi($verb, $apiurl, $query = '', $xml = '')
    {
        // Wrapper for Guzzle API Calls
        $client = new GuzzleHttpClient();

        $options = [
            'auth'    => [env('UCCX_FINESSE_USER'), env('UCCX_FINESSE_PASS')],
            'verify'  => false,
            'headers' => [
                //'Content-Type'     => 'application/xml',
                'Accept'           => 'application/xml',
            ],
            //'debug' => true,
            //'http_errors' => true,
        ];
        if ($query != '') {
            $options['query'] = $query;
        }
        if ($xml != '') {
			$options['headers'] = ['Content-Type'     => 'application/xml'];
            $options['body'] = $xml;
        }

        $response = [];

        try {
            $apiRequest = $client->request($verb, $apiurl, $options);

            print $apiurl.PHP_EOL;

            $response['status_code'] = $apiRequest->getStatusCode();

            $xml = $apiRequest->getBody()->getContents();
			//print_r($xml);
            $xml = simplexml_load_string($xml);
            $result = json_encode($xml);

            //print_r($result);

            $result = json_decode($result, true);

            //print_r($result);

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

    public static function getUser($server, $userid)
    {
        $verb = 'GET';
        $apiurl = "{$server}:8445/finesse/api/User/{$userid}";
        $query = '';
        $xml = '';

        return self::wrapapi($verb, $apiurl, $query);
    }
	
	public static function userLogin($server, $userid, $extension)
    {
		$UPDATE = [
			'User' => [	'state' 	=> "LOGIN",
						'extension'	=> $extension,
					]
		];
		
		$UPDATE = <<<END
<User>
<state>LOGIN</state>
<extension>{$extension}</extension>
</User>
END;
        //print_r($UPDATE);
		$verb = 'PUT';
        $apiurl = "{$server}:8445/finesse/api/User/{$userid}";
        $query = '';
        $xml = $UPDATE;

        return self::wrapapi($verb, $apiurl, $query, $xml);
    }
	
	public static function userLogout($server, $userid, $extension)
    {
		$UPDATE = [
			'User' => [	'state' 	=> "LOGOUT",
						'extension'	=> $extension,
					]
		];

		$UPDATE = <<<END
<User>
<state>LOGOUT</state>
<extension>{$extension}</extension>
</User>
END;
        $verb = 'PUT';
        $apiurl = "{$server}:8445/finesse/api/User/{$userid}";
        $query = '';
        $xml = $UPDATE;

        return self::wrapapi($verb, $apiurl, $query, $xml);
    }
}
