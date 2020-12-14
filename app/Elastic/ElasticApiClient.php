<?php

namespace App\Elastic;

use GuzzleHttp\Client;

class ElasticApiClient
{
    private $host;
    private $username;
    private $password;

    private $baseurl;
    private $headers;
    private $auth;

    private $guzzle;

    /*
        TODOs - Clean up variables, validate user input.
    */
    public function __construct($host, $username, $password)
    {
        // checking for dumb... could make this alot better
        if (! $host || ! $username || ! $password) {
            throw new \Exception('rest api client requires idm hostname, username, and password');
        }

        $this->host = $host;
        $this->username = $username;
        $this->password = $password;

        $this->baseurl = $this->host;
        $this->headers = [
            'Accept'       => 'application/json',
            //'X-CSRF-Token' => 'Fetch',
        ];
        $this->auth = [
            $this->username,
            $this->password,
        ];

        // Create our HTTP client to send requests with
        /*$options = [
            'cookies' => true,
        ];*/
        $this->guzzle = new \GuzzleHttp\Client();
    }


    public function postNetworkData($json)
    {
		if(json_decode($json, true)){
			
			try{
				
				$url = $this->baseurl."/network/_doc";
				// set the mandatory headers...
				$headers = $this->headers;

				// add application/json to content type of this request
				$headers['Content-Type'] = 'application/json';

				// Build the request with json body and headers
				$options = [
					'headers' => $headers,
					'body'    => $json,
					//'debug' => true,
				];

				// send the request
				$response = $this->guzzle->request('POST', $url, $options);

				$body = $response->getBody();
				//$resp = json_decode($body, true);

				return $body;
				
			}catch (Exception $e) {
				echo 'Caught exception: ',  $e->getMessage(), "\n";
			}
		}

    }
}