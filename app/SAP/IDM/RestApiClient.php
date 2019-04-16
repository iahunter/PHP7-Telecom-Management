<?php

/**
 * SAP IDM Rest API Client
 *
 * PHP version 7
 *
 * Copy data from other places and represent it in one database for reporting
 *
 * @category  default
 * @author    Metaclassing <Metaclassing@SecureObscure.com>
 * @copyright 2018-2018 @authors
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace App\SAP\IDM;

use GuzzleHttp\Client;

class RestApiClient
{
    private $idm;
    private $username;
    private $password;

    private $baseurl;
    private $headers;
    private $auth;

    private $guzzle;

	/* 
		TODOs - Clean up variables, validate user input. 
	*/
    public function __construct($idm, $username, $password)
    {
        // checking for dumb... could make this alot better
        if (!$idm || !$username || !$password) {
            throw new \Exception('vmware rest api client requires idm hostname, username, and password');
        }
        // what if we cant ping idm?
        $this->idm = $idm;
        $this->username = $username;
        $this->password = $password;

        $this->baseurl = 'http://'.$this->idm;
        $this->headers = [
            'Accept' => 'application/json',
			'X-CSRF-Token' => 'Fetch',
        ];
        $this->auth = [
            $this->username,
            $this->password,
        ];

        // Create our HTTP client to send requests with
        $options = [
            'cookies' => true,
        ];
        $this->guzzle = new \GuzzleHttp\Client($options);
    }
	
	public function validate($input, $type = "string")
    {
		// Validate User Input. 
		$types = ['string', 'number']; 
		
		if(!in_array($type, $types)){
			throw new \Exception('Invalid validation type'); 
		}
		if ($type == "string") {
			// If type string then trim and filter bad charactors. 
			$string = trim(htmlspecialchars($input));
			return $string; 
		}
		if ($type == "number") {
			$number = filter_var($input, FILTER_VALIDATE_INT);
			if ($number === false) {
				throw new \Exception('Not a valid number'); 
			}
			return $input; 
		}
    }
	
	// Function to get the x-csrf-token from the Get and update our headers with the token
	public function updateHeaders($headers)
    {
		if(isset($headers['x-csrf-token'])){
			$this->headers['X-CSRF-Token'] = $headers['x-csrf-token']; 
		}
    }

    public function getUserID($alias)
    {
		$alias = $this->validate($alias, "string"); 

        $url = $this->baseurl.'/idmrestapi/v2/service/ET_MX_PERSON'; 
        // set the mandatory headers...
		
        $options = [
			'auth' => $this->auth,
            'headers' => $this->headers,
			'query' 	=> ['$filter' => "SV_Z_KIEWITID eq '$alias'", 
							'$format' => 'json', 
							'$select' => 'ID'
							], 
        ];
        // send the request
        $response = $this->guzzle->request('GET', $url, $options);

        // decode the response into a useful structure
        $body = $response->getBody();

		$this->updateHeaders($response->getHeaders()); 
		
        $results = json_decode($body, true);

		// Count the number of records we get back. 
		$count = count($results['d']['results']); 
		
		if($count != 1){
			throw new \Exception("We recieved a count of {$count} while expecting 1 record. The username you submitted is not correct"); 
		}

		$userid = $results['d']['results'][0]['ID']; 
        return $userid;
    }

    public function getUserPhone($id, $guid)
    {
		$id = $this->validate($id, "number"); 
		$guid = $this->validate($guid, "string"); 
		
        $url = $this->baseurl."/idmrestapi/v2/service/ET_MX_PERSON(ID={$id},TASK_GUID=guid'{$guid}')"; 
        // set the mandatory headers...
		
        $options = [
			'auth' => $this->auth,
            'headers' => $this->headers,
			'query' 	=> [
							'$format' => 'json', 
							'$select' => 'SV_MX_PHONE_PRIMARY'
							], 
        ];
        // send the request
        $response = $this->guzzle->request('GET', $url, $options);
		
		// Update our headers. 
		$this->updateHeaders($response->getHeaders()); 
		
        // decode the response into a useful structure
        $body = $response->getBody();
		
		
		
        $results = json_decode($body, true);
		$number = $results['d']['SV_MX_PHONE_PRIMARY']; 
        return $number;
    }
	
    public function updateUserPhone($id, $guid, $number)
    {
		$id = $this->validate($id, "number"); 
		$guid = $this->validate($guid, "string"); 
		$number = $this->validate($number, "string"); 
		
        $url = $this->baseurl."/idmrestapi/v2/service/ET_MX_PERSON(ID={$id},TASK_GUID=guid'{$guid}')";
        // set the mandatory headers...
        $headers = $this->headers;
		
        // add application/json to content type of this request
        $headers['Content-Type'] = 'application/json';
		
		// Add the HTTP MERGE Method Header
		$headers['X-HTTP-METHOD'] = 'MERGE';
        
		// build our body. 
        $body = [
				'SV_MX_PHONE_PRIMARY' => $number,
				];
				
        $jsonbody = json_encode($body);

        // Build the request with json body and headers
        $options = [
            'headers' => $headers,
            'body' => $jsonbody,
            //'debug' => true,
        ];

        // send the request
        $response = $this->guzzle->request('POST', $url, $options);
		
		$this->updateHeaders($response->getHeaders()); 
        // decode the response into a useful structure
        $body = $response->getBody();
        //$resp = json_decode($body, true);

        return $body;
    }
}