<?php

namespace App\Gizmo;

use \GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class RestApiClient
{
	private $headers; 
    private $guzzle;
	private $gizmo;
	private $token; 
	private $oauth2;
	private $msTenant;

    /*
        TODOs - Clean up variables, validate user input.
    */
    public function __construct($msTenant, $url, $clientId, $clientSecret, $scope)
    {

		$this->guzzle = new \GuzzleHttp\Client;
		
		$this->msTenant = $msTenant; 
		
		$this->gizmo = $url; 
		
		$this->oauth2 = [
			'form_params' => [
				'grant_type' => 'client_credentials',
				'client_id' => $clientId,
				'client_secret' => $clientSecret,
				'scope' => $scope,
			]
		];

    }
	
	public function get_oauth2_token()
	{
		// Check to see if we have a token in our cache. 
		$token = Cache::get('msoauth_token');
		
		//print_r($token); 
		
		if($token){
			$this->token = $token;  
			//print "Got token from Cache".PHP_EOL; 
			//\Log::info('queueTeamsMACD', ['cachedtoken' => $token]);
		}else{
			//print "No token found in Cache... Getting Token from Microsoft".PHP_EOL;
			// Get a client token from Microsoft OAUTH2
			$response = $this->guzzle->post("https://login.microsoftonline.com/{$this->msTenant}/oauth2/v2.0/token", $this->oauth2);
		
			//$token = json_decode((string) $response->getBody(), true)['access_token'];
			$array = json_decode((string) $response->getBody(), true);
			//print_r($array); 
			
			$this->token = $array['access_token']; 
			
			//print_r($this->token); 
			
			$token_expires_in = $array['expires_in']*.95;

			//print "Got new token that expires in {$array['expires_in']} seconds".PHP_EOL; 
			
			
			\Log::info('queueTeamsMACD', ['newtoken' => $this->token]);

			// Cache token for 30 mins. 
			$time = Carbon::now()->addSeconds($token_expires_in);
			
			\Log::info('queueTeamsMACD', ['cachetime' => $time]);
			
			Cache::put('msoauth_token', $this->token, $time);
		}
		
		$this->options = ['headers' => 
								[
									'Content-Type' => 'application/json',
									'Accept' => 'application/json',
									'Authorization' => "Bearer {$this->token}",
								
								]
							];
	}
	
	// Get Teams User by ID
    public function get_teams_csonline_user_by_userid($userid)
    {
		$this->get_oauth2_token();  
		
		$userid = trim(htmlspecialchars($userid));
		
		$url = $this->gizmo . '/api/teams/csonlineuser/'. $userid; 
		
        $response = $this->guzzle->request('GET', $url, $this->options);
		
		return json_decode((string) $response->getBody(), true);
	}

	// Get Teams Users that are voice enabled
    public function get_teams_csonline_users_voice_enabled()
    {
		$this->get_oauth2_token(); 
		
		$url = $this->gizmo . '/api/teams/voiceenabled';

        $response = $this->guzzle->request('GET', $url, $this->options);
		
		return json_decode((string) $response->getBody(), true);
	}
	
	// Get All Teams Users
    public function get_teams_csonline_all_users()
    {
		$this->get_oauth2_token(); 
		
		$url = $this->gizmo . '/api/teams/csonlineuser';

        $response = $this->guzzle->request('GET', $url, $this->options);
		
		return json_decode((string) $response->getBody(), true);
	}
	
	// Get All Teams Users in NPA NXX OnPremLineURI
    public function get_teams_csonline_all_users_by_NPA_NXX($NPA_NXX)
    {
		$this->get_oauth2_token(); 
		
		$url = $this->gizmo . "/api/teams/search/onpremlineuri/{$NPA_NXX}";

        $response = $this->guzzle->request('GET', $url, $this->options);
		
		return json_decode((string) $response->getBody(), true);
	}
	
	// Get All Teams Users in NPA NXX OnPremLineURI
    public function get_teams_csonline_user_by_sip_address($sipaddress)
    {
		$this->get_oauth2_token(); 
		
		$url = $this->gizmo . "/api/teams/search/sipaddress/{$sipaddress}";

        $response = $this->guzzle->request('GET', $url, $this->options);
		
		return json_decode((string) $response->getBody(), true);
	}
	
	
	// Setup Voice Calling for User
    public function set_teams_user($body)
    {
		$this->get_oauth2_token(); 
		
		\Log::info('GizmoApi', ['body' => $body]);
		
		if(!json_decode($body, true)){
			throw new \Exception('Not Valid JSON'); 
		}

		$url = $this->gizmo . '/api/teams/csonlineuser';

		$options = $this->options; 
		
		$options['body'] = $body; 
		
        $response = $this->guzzle->request('POST', $url, $options);
		
		\Log::info('GizmoApi', ['data' => json_decode((string) $response->getBody(), true)]);
		
		return json_decode((string) $response->getBody(), true);
	}
	
	// Create Civic Address
    public function create_civic_address($body)
    {
		$this->get_oauth2_token(); 
		
		if(!json_decode($body, true)){
			throw new \Exception('Not Valid JSON'); 
		}

		$url = $this->gizmo . '/api/e911/csonlinecivicaddress/new';

		$options = $this->options; 
		
		$options['body'] = $body; 
		
        $response = $this->guzzle->request('POST', $url, $options);
		
		return json_decode((string) $response->getBody(), true);
	}
	
	// Delete Civic Address
    public function delete_civic_address_by_id($id)
    {
		$this->get_oauth2_token(); 
		
		$url = $this->gizmo . '/api/e911/csonlinecivicaddress';

		$options = $this->options; 
		
		$body = ["CivicAddressId" => $id]; 
		
		$options['body'] = json_encode($body); 
		
        $response = $this->guzzle->request('DELETE', $url, $options);
		
		return json_decode((string) $response->getBody(), true);
	}
}
