<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// Include the JWT Facades shortcut
use Tymon\JWTAuth\Facades\JWTAuth;

class Cucmsite extends Cucm
{
	
	public $results;
	
	
    public function listsites()
    {
        $user = JWTAuth::parseToken()->authenticate();

        try {
            $sites = $this->cucm->get_site_names();

            if (! count($sites)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
        } catch (\Exception $e) {
            echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $sites,
                    ];

        return response()->json($response);
    }

    public function getSite(Request $request, $name)
    {
        $user = JWTAuth::parseToken()->authenticate();
		
		$name = strtoupper($name);

        try {
            $site = $this->cucm->get_all_object_types_by_site($name);
            if (! count($site)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
        } catch (\Exception $e) {
            echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }


        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $site,
                    ];

        return response()->json($response);
    }
	
	
	public function getSiteDetails(Request $request, $name)
    {
        $user = JWTAuth::parseToken()->authenticate();
		
		$name = strtoupper($name);

        try {
            $site = $this->cucm->get_all_object_type_details_by_site($name);
            if (! count($site)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
        } catch (\Exception $e) {
            echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }


        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $site,
                    ];

        return response()->json($response);
    }
	
	
	public function createSite(Request $request)
	{

		$user = JWTAuth::parseToken()->authenticate();
		
		$SITE_TYPE = $request->type;
		
		// If the SRST IP is set, has contents, and validates as an IP address
		if(isset($request->srstip) && !filter_var($request->srstip,FILTER_VALIDATE_IP)) {
			return "Error: SRST invalid";
		}
		elseif(isset($request->srstip))
		{
			$SRSTIP = $request->srstip;
		}
		else
		{
			$SRSTIP = "";
		}
		

		// Turn the users text into an array of IP addresses
		$H323TEXT = "";
		$H323LIST = [];
		if(isset($request->h323ip) && $request->h323ip) {
			$H323TEXT = $request->h323ip;
		}
		$H323LIST = preg_split( '/\r\n|\r|\n/', $H323TEXT );

		// Loop through H323 IP addresses in an array and validate them as IPs
		foreach($H323LIST as $KEY => $H323IP) {
			// If the line is blank rip it out of the list
			if(trim($H323IP) == "") {
				unset($H323LIST[$KEY]);
				continue;
			}
			// If the line has content but is NOT an ip address, abort
			if(!filter_var($H323IP,FILTER_VALIDATE_IP) ) {
				return "Error, one of the H323 IPs provided is not valid: {$H323IP}";
			}
		}
		$H323LIST = array_values($H323LIST);

		// Check their timezone
		if(!isset($request->timezone) || !$request->timezone) {
			return "Error, no timezone selected";
		}
		$TIMEZONE = $request->timezone;

		// Check their NPA
		if(!isset($request->npa) || !$request->npa) {
			return "Error, no npa selected";
		}
		$NPA = $request->npa;

		// Check their NXX
		if(!isset($request->nxx) || !$request->nxx) {
			return "Error, no nxx selected";
		}
		$NXX = $request->nxx;

		// Turn the users text into an array of translation patterns
		$DIDTEXT = "";
		if(isset($request->didrange) && $request->didrange) {
			$DIDTEXT = $request->didrange;
		}else{
			return "No DID ranges provided";
		}
		$DIDLIST = preg_split( '/\r\n|\r|\n/', $DIDTEXT );
		if(!count($DIDLIST)) {
			return "No DID ranges found";
		}
		// Loop through translation pattern DID sections and validate them against callmanagers data dictionary
		foreach($DIDLIST as $KEY => $DID) {
			// Trim off any whitespace around the DID range
			$DID = trim($DID);
			$DIDLIST[$KEY] = $DID;
			// If the line is blank rip it out of the list
			if(!$DID) {
				unset($DIDLIST[$KEY]);
				continue;
			}
			// If the line has content but is NOT a valid DID thing, then abort
			$REGEX = "/^[]0-9X[-]{4,14}$/"; // This is from CUCM 10.5's data dictionary... and modified
			if(!preg_match($REGEX,$DID) ) {
				return "Error, one of the DID ranges provided is not valid: {$DID}";
			}
		}
		$DIDLIST = array_values($DIDLIST);

		// Check for an optional operator extension
		$OPERATOR = "";
		if(isset($request->operator) && $request->operator) {
			$OPERATOR = $request->operator;
		}

		// Use user input to decide what CUCM subscribers to home this new site to

		// If the users site code is KHO, dump them on our subscribers
		$SITECODE = strtoupper($request->sitecode);
		if(substr($SITECODE,0,2) == "KHO") {
			$CUCM1 = "KHONEMDCVCS02";
			$CUCM2 = "KHONESDCVCS06";
		// Otherwise if they are KOS, dump them there
		}elseif( substr($SITECODE,0,2) == "KOS" ){
			$CUCM1 = "KHONESDCVCS04";
			$CUCM2 = "KHONEMDCVCS05";
		// Otherwise if they are EAST or CENTRAL time
		}elseif(preg_match("/(eastern|central)+/i",$TIMEZONE)){
			$CUCM1 = "KHONEMDCVCS01";
			$CUCM2 = "KHONESDCVCS06";
		}else{
			$CUCM1 = "KHONESDCVCS03";
			$CUCM2 = "KHONEMDCVCS05";
		}


		// Final user information required to provision a CUCM SITE:
		$result = $this->provision_cucm_site_axl(
		//$result = [
												$SITECODE,
												$SRSTIP,
												$H323LIST,
												$TIMEZONE,
												$NPA,
												$NXX,
												$DIDLIST,
												$CUCM1,
												$CUCM2,
												$OPERATOR
												);
		//	];									
		$response = [
			'status_code'    => 200,
			'success'        => true,
			'message'        => '',
			'response'       => $result,
			];

        return response()->json($response);
	}
	
	
	function wrap_add_object($DATA,$TYPE,$SITE) {
		try{
			//print "Attempting to add a {$TYPE} for {$SITE}:";
			$REPLY = $this->cucm->add_object_type_by_assoc($DATA,$TYPE);
			$this->results[$TYPE] = "{$TYPE} CREATED: {$REPLY}\n\n";
		}catch (\Exception $E) {
			$EXCEPTION = "Exception adding object type {$TYPE} for site {$SITE}:" .
				  "{$E->getMessage()}" .
				  "Stack trace:\n" .
				  "{$E->getTraceAsString()}" .
				  "Data sent:\n";
				  $DATA[$TYPE]['exception'] = $EXCEPTION;
				  $this->results[$TYPE] = $DATA;
		}
	}
	
	
	// Build all the elements needed for the site. 
	
	private function provision_cucm_site_axl(
												$SITE,
												$SRSTIP,
												$H323LIST,
												$TIMEZONE,
												$NPA,
												$NXX,
												$DIDLIST,
												$CUCM1,
												$CUCM2,
												$OPERATOR
											)
	{

		// Check if the site exists in the CUCM database first. 
		$site_array = $this->cucm->get_all_object_types_by_site($SITE);
	
		// 1 - Add a SRST router

		// Calculated data structure
		$TYPE = "Srst";
		$DATA = [
				"name"		=> "SRST_{$SITE}",
				"ipAddress"	=> $SRSTIP,
				"port"		=> 2000,
				"SipPort"	=> 5060,
				];
				
		// Check if the object already exists. If it isn't then add it. 
		if(!empty($site_array[$TYPE])){
			if(in_array($DATA['name'],$site_array[$TYPE])){
				$this->results[$TYPE] = "Skipping... {$DATA['name']} already exists.";
			}else{
				$this->wrap_add_object($DATA,$TYPE,$SITE);
			}
		}else{
			$this->wrap_add_object($DATA,$TYPE,$SITE);
		}
		
		


		// 2 - Add a route partition

		// Calculated variables
		$TYPE = "RoutePartition";
		// Prepared datastructure
		$DATA = [
				"name"							=> 'PT_'.$SITE,
				"description"					=> $SITE,
				"useOriginatingDeviceTimeZone"	=> "true",
				];
				
		// Check if the object already exists. If it isn't then add it. 
		if(!empty($site_array[$TYPE])){
			if(in_array($DATA['name'],$site_array[$TYPE])){
				$this->results[$TYPE] = "Skipping... {$DATA['name']} already exists.";
			}else{
				$this->wrap_add_object($DATA,$TYPE,$SITE);
			}
		}else{
			$this->wrap_add_object($DATA,$TYPE,$SITE);
		}

		
		// 2.1 - Add a 911 route partition

		// Calculated variables
		$TYPE = "RoutePartition";
		// Prepared datastructure
		$DATA = [
				'name'                            => 'PT_'.$SITE.'_911',
				'description'                     => $SITE.' 911 Calling',
				'useOriginatingDeviceTimeZone'    => 'true',
				];

		// Check if the object already exists. If it isn't then add it. 
		if(!empty($site_array[$TYPE])){
			if(in_array($DATA['name'],$site_array[$TYPE])){
				$this->results[$TYPE] = "Skipping... {$DATA['name']} already exists.";
			}else{
				$this->wrap_add_object($DATA,$TYPE,$SITE);
			}
		}else{
			$this->wrap_add_object($DATA,$TYPE,$SITE);
		}


		// 3 - Add a CSS

		// Calculated variables
		$TYPE = "Css";
		// Prepared datastructure
		$DATA = [
				"name"			=> "CSS_{$SITE}",
				"description"	=> "CSS for {$SITE}",
				"members"		=> [
									"member" => [
													[
													"routePartitionName"=> "PT_{$SITE}",
													"index"				=> 1,
													],
													[
													"routePartitionName"=> "Global-All-Lines",
													"index"				=> 2,
													],
													[
													"routePartitionName"=> 'PT_'.$SITE.'_911',
													"index"				=> 3,
													],
												],
									],
				];
		// Check if the object already exists. If it isn't then add it. 
		if(!empty($site_array[$TYPE])){
			if(in_array($DATA['name'],$site_array[$TYPE])){
				$this->results[$TYPE] = "Skipping... {$DATA['name']} already exists.";
			}else{
				$this->wrap_add_object($DATA,$TYPE,$SITE);
			}
		}else{
			$this->wrap_add_object($DATA,$TYPE,$SITE);
		}

		// 4 - Add a location

		// Calculated variables
		$TYPE = "Location";
		// Prepared datastructure
		$DATA = [
				"name"					=> "LOC_{$SITE}",
				"withinAudioBandwidth"	=> "0",
				"withinVideoBandwidth"	=> "0",
				"withinImmersiveKbits"	=> "0",
				"betweenLocations"		=> [],
				];
		// Check if the object already exists. If it isn't then add it. 
		if(!empty($site_array[$TYPE])){
			if(in_array($DATA['name'],$site_array[$TYPE])){
				$this->results[$TYPE] = "Skipping... {$DATA['name']} already exists.";
			}else{
				$this->wrap_add_object($DATA,$TYPE,$SITE);
			}
		}else{
			$this->wrap_add_object($DATA,$TYPE,$SITE);
		}

		// 5 - Add a region

		// Calculated variables
		$TYPE = "Region";
		// Prepared datastructure
		$DATA = [
				"name"				=> "R_{$SITE}",
				"relatedRegions"	=> [
										"relatedRegion" => [
																[
																"regionName"				=> "Default",
																"bandwidth"					=> "G.729",
																"videoBandwidth"			=> "384",
																"lossyNetwork"				=> "",
																"codecPreference"			=> "",
																"immersiveVideoBandwidth"	=> "",
																],
																[
																"regionName"				=> "R_711",
																"bandwidth"					=> "G.711",
																"videoBandwidth"			=> "384",
																"lossyNetwork"				=> "",
																"codecPreference"			=> "",
																"immersiveVideoBandwidth"	=> "",
																],
																[
																"regionName"				=> "R_729",
																"bandwidth"					=> "G.729",
																"videoBandwidth"			=> "384",
																"lossyNetwork"				=> "",
																"codecPreference"			=> "",
																"immersiveVideoBandwidth"	=> "",
																],
																[
																"regionName"				=> "R_{$SITE}",
																"bandwidth"					=> "G.711",
																"videoBandwidth"			=> "384",
																"lossyNetwork"				=> "",
																"codecPreference"			=> "",
																"immersiveVideoBandwidth"	=> "",
																],
																[
																"regionName"				=> "R_FAX",
																"bandwidth"					=> "G.711",
																"videoBandwidth"			=> "384",
																"lossyNetwork"				=> "",
																"codecPreference"			=> "",
																"immersiveVideoBandwidth"	=> "",
																],
																[
																"regionName"				=> "R_GW",
																"bandwidth"					=> "G.711",
																"videoBandwidth"			=> "384",
																"lossyNetwork"				=> "",
																"codecPreference"			=> "",
																"immersiveVideoBandwidth"	=> "",
																],
																[
																"regionName"				=> "R_Voicemail",
																"bandwidth"					=> "G.729",
																"videoBandwidth"			=> "384",
																"lossyNetwork"				=> "",
																"codecPreference"			=> "",
																"immersiveVideoBandwidth"	=> "",
																],
															],
										],
				];
		// Check if the object already exists. If it isn't then add it. 
		if(!empty($site_array[$TYPE])){
			if(in_array($DATA['name'],$site_array[$TYPE])){
				$this->results[$TYPE] = "Skipping... {$DATA['name']} already exists.";
			}else{
				$this->wrap_add_object($DATA,$TYPE,$SITE);
			}
		}else{
			$this->wrap_add_object($DATA,$TYPE,$SITE);
		}


		// 6 - Add a call mangler group

		// Calculated variables
		$TYPE = "CallManagerGroup";
		// Prepared datastructure
		$DATA = [
				"name"		=> "CMG-{$SITE}",
				"members"	=> [
								"member" => [
												[
												"callManagerName"	=> $CUCM1,
												"priority"			=> "1",
												],
												[
												"callManagerName"	=> $CUCM2,
												"priority"			=> "2",
												],
											],
								],
				];
		// Check if the object already exists. If it isn't then add it. 
		if(!empty($site_array[$TYPE])){
			if(in_array($DATA['name'],$site_array[$TYPE])){
				$this->results[$TYPE] = "Skipping... {$DATA['name']} already exists.";
			}else{
				$this->wrap_add_object($DATA,$TYPE,$SITE);
			}
		}else{
			$this->wrap_add_object($DATA,$TYPE,$SITE);
		}

		// 7 - Add a device pool

		// Calculated variables
		$TYPE = "DevicePool";
		// Prepared datastructure
		$DATA = [
				"name"					=> "DP_{$SITE}",
				"dateTimeSettingName"	=> $TIMEZONE,
				"callManagerGroupName"	=> "CMG-{$SITE}",
				"regionName"			=> "R_{$SITE}",
				"srstName"				=> "SRST_{$SITE}",
				"locationName"			=> "LOC_{$SITE}",
				];
		// Check if the object already exists. If it isn't then add it. 
		if(!empty($site_array[$TYPE])){
			if(in_array($DATA['name'],$site_array[$TYPE])){
				$this->results[$TYPE] = "Skipping... {$DATA['name']} already exists.";
			}else{
				$this->wrap_add_object($DATA,$TYPE,$SITE);
			}
		}else{
			$this->wrap_add_object($DATA,$TYPE,$SITE);
		}

		// 8 - Add a conference bridge

		// Calculated variables
		$TYPE = "ConferenceBridge";
		// Prepared datastructure
		$DATA = [
				"name"			=> "{$SITE}_CFB",
				"description"	=> "Conference bridge for {$SITE}",
				"product"		=> "Cisco IOS Enhanced Conference Bridge",
				"devicePoolName"=> "DP_{$SITE}",
				"locationName"	=> "LOC_{$SITE}",
				];
		// Check if the object already exists. If it isn't then add it. 
		if(!empty($site_array[$TYPE])){
			if(in_array($DATA['name'],$site_array[$TYPE])){
				$this->results[$TYPE] = "Skipping... {$DATA['name']} already exists.";
			}else{
				$this->wrap_add_object($DATA,$TYPE,$SITE);
			}
		}else{
			$this->wrap_add_object($DATA,$TYPE,$SITE);
		}

		// 9 - Add media termination point 1

		// Calculated variables
		$TYPE = "Mtp";
		// Prepared datastructure
		$DATA = [
				"name"				=> "{$SITE}_729",
				"description"		=> "G729 MTP for {$SITE}",
				"mtpType"			=> "Cisco IOS Enhanced Software Media Termination Point",
				"devicePoolName"	=> "DP_{$SITE}",
				"trustedRelayPoint"	=> "false",
				];
		// Check if the object already exists. If it isn't then add it. 
		if(!empty($site_array[$TYPE])){
			if(in_array($DATA['name'],$site_array[$TYPE])){
				$this->results[$TYPE] = "Skipping... {$DATA['name']} already exists.";
			}else{
				$this->wrap_add_object($DATA,$TYPE,$SITE);
			}
		}else{
			$this->wrap_add_object($DATA,$TYPE,$SITE);
		}

		// 10 - Add media termination point 2

		// Calculated variables
		$TYPE = "Mtp";
		// Prepared datastructure
		$DATA = [
				"name"				=> "{$SITE}_711",
				"description"		=> "G711 MTP for {$SITE}",
				"mtpType"			=> "Cisco IOS Enhanced Software Media Termination Point",
				"devicePoolName"	=> "DP_{$SITE}",
				"trustedRelayPoint"	=> "false",
				];
		// Check if the object already exists. If it isn't then add it. 
		if(!empty($site_array[$TYPE])){
			if(in_array($DATA['name'],$site_array[$TYPE])){
				$this->results[$TYPE] = "Skipping... {$DATA['name']} already exists.";
			}else{
				$this->wrap_add_object($DATA,$TYPE,$SITE);
			}
		}else{
			$this->wrap_add_object($DATA,$TYPE,$SITE);
		}

		// 11 - Add a media resource group

		// Calculated variables
		$TYPE = "MediaResourceGroup";
		// Prepared datastructure
		$DATA = [
				"name"			=> "MRG_{$SITE}",
				"description"	=> "{$SITE} Media Resources",
				"multicast"		=> "false",
				"members"		=> [
									"member" => [
													[
													"deviceName"	=> "{$SITE}_711",
													],
													[
													"deviceName"	=> "{$SITE}_729",
													],
													[
													"deviceName"	=> "{$SITE}_CFB",
													],
												],
									],
				];
		// Check if the object already exists. If it isn't then add it. 
		if(!empty($site_array[$TYPE])){
			if(in_array($DATA['name'],$site_array[$TYPE])){
				$this->results[$TYPE] = "Skipping... {$DATA['name']} already exists.";
			}else{
				$this->wrap_add_object($DATA,$TYPE,$SITE);
			}
		}else{
			$this->wrap_add_object($DATA,$TYPE,$SITE);
		}

		// 12 - Add a media resource list

		// Calculated variables
		$TYPE = "MediaResourceList";
		// Prepared datastructure
		$DATA = [
				"name"			=> "MRGL_{$SITE}",
				"members"		=> [
									"member"	=> [
														[
														"mediaResourceGroupName"	=> "MRG_{$SITE}",
														"order"						=> "0",
														],
														[
														"mediaResourceGroupName"	=> "MRG_Sub1_Resources",
														"order"						=> "1",
														],
														[
														"mediaResourceGroupName"	=> "MRG_Pub_Resources",
														"order"						=> "2",
														],
													],
									],
				];
		// Check if the object already exists. If it isn't then add it. 
		if(!empty($site_array[$TYPE])){
			if(in_array($DATA['name'],$site_array[$TYPE])){
				$this->results[$TYPE] = "Skipping... {$DATA['name']} already exists.";
			}else{
				$this->wrap_add_object($DATA,$TYPE,$SITE);
			}
		}else{
			$this->wrap_add_object($DATA,$TYPE,$SITE);
		}

		// 13 - Add H323 Gateways

		$ROUTERMODEL = "Cisco 2951";
		// Calculated variables
		$TYPE = "H323Gateway";
		// Prepared datastructure
		foreach($H323LIST as $H323IP) {
			$DATA = [
					"name"						=> $H323IP,
					"description"				=> "{$SITE} {$H323IP} {$ROUTERMODEL}",
					"callingSearchSpaceName"	=> "CSS_{$SITE}",
					"devicePoolName"			=> "DP_{$SITE}",
					"locationName"				=> "LOC_{$SITE}",
					"product"					=> "H.323 Gateway",
					"class"						=> "Gateway",
					"protocol"					=> "H.225",
					"protocolSide"				=> "Network",
					"signalingPort"				=> "1720",
					"tunneledProtocol"			=> "",
					"useTrustedRelayPoint"		=> "",
					"packetCaptureMode"			=> "",
					"callingPartySelection"		=> "",
					"callingLineIdPresentation"	=> "",
					"calledPartyIeNumberType"	=> "",
					"callingPartyIeNumberType"	=> "",
					"calledNumberingPlan"		=> "",
					"callingNumberingPlan"		=> "",
					];
			// Check if the object already exists. If it isn't then add it. 
			if(!empty($site_array[$TYPE])){
				if(in_array($DATA['name'],$site_array[$TYPE])){
					$this->results[$TYPE] = "Skipping... {$DATA['name']} already exists.";
				}else{
					$this->wrap_add_object($DATA,$TYPE,$SITE);
				}
			}else{
				$this->wrap_add_object($DATA,$TYPE,$SITE);
			}
		}


		// 14 - Add a route group

		// Calculated variables
		$TYPE = "RouteGroup";
		// Prepared datastructure
		$i = 1;
		if(count($H323LIST) <= 1){
			foreach($H323LIST as $H323IP) {
				$H323MEMBER = [
								"deviceName"			=> $H323IP,
								// Increment order @ each iteration through previous loop!
								"deviceSelectionOrder"	=> $i++,
								"port"					=> "0",
								];
			}
			$DATA = [
				"name"					=> "RG_{$SITE}",
				"distributionAlgorithm"	=> "Top Down",
				"members"				=> [
											"member"	=> $H323MEMBER,
											],
				];
		}else{
			$DATA = [
				"name"					=> "RG_{$SITE}",
				"distributionAlgorithm"	=> "Top Down",
				"members"				=> [
											"member"	=> [],
											],
				];
			// Calculate multiple members to add to this array with order numbers
			
			foreach($H323LIST as $H323IP) {
				$H323MEMBER = [
								"deviceName"			=> $H323IP,
								// Increment order @ each iteration through previous loop!
								"deviceSelectionOrder"	=> $i++,
								"port"					=> "0",
								];
				array_push($DATA["members"]["member"],$H323MEMBER);
			}
		}
		
		
		
		
		// Check if the object already exists. If it isn't then add it. 
		if(!empty($site_array[$TYPE])){
			if(in_array($DATA['name'],$site_array[$TYPE])){
				$this->results[$TYPE] = "Skipping... {$DATA['name']} already exists.";
			}else{
				$this->wrap_add_object($DATA,$TYPE,$SITE);
			}
		}else{
			$this->wrap_add_object($DATA,$TYPE,$SITE);
		}


		// 15 - Update an existing device pool to add the new route group above

		// Calculated variables
		$TYPE = "DevicePool";
		// Update these fields in the device pool object for this site
		$DATA = [
				"name"					=> "DP_{$SITE}",
				"mediaResourceListName"	=> "MRGL_{$SITE}",
				"localRouteGroup"		=> [
											"name"		=> "Standard Local Route Group",
											"value"		=> "RG_{$SITE}",
											],
				];
		// Run the update operation
		try{
			//print "Attempting to update object type {$TYPE} for {$SITE}:";
			$REPLY = $this->cucm->update_object_type_by_assoc($DATA,$TYPE);
			$this->results['DevicePoolUpdate'] = "{$TYPE} UPDATED: {$REPLY}";
		}catch (\Exception $E) {
			$EXCEPTION = "Exception updating object type {$TYPE} for site {$SITE}:" .
				  "{$E->getMessage()}" .
				  "Stack trace:\n" .
				  "{$E->getTraceAsString()}" .
				  "Data sent:\n";
				  $DATA[$TYPE]['exception'] = $EXCEPTION;
				  $this->results[$TYPE] = $DATA;
		}
		


		// 16 - Create our translation patterns from user input

		// Calculated variables
		$TYPE = "TransPattern";

		// Prepare and add datastructures
		foreach($DIDLIST as $PATTERN) {
			$DATA = [
					"routePartitionName"			=> "PT_{$SITE}",
					"pattern"						=> $PATTERN,
					"calledPartyTransformationMask"	=> "{$NPA}{$NXX}XXXX",
					"callingSearchSpaceName"		=> "CSS_{$SITE}",
					"description"					=> "{$SITE} dial pattern {$PATTERN}",
					"usage"							=> "Translation",
					];
			// Check if the object already exists. If it isn't then add it. 
			if(!empty($site_array[$TYPE])){
				if(in_array($DATA['pattern'],$site_array[$TYPE])){
					$this->results[$TYPE] = "Skipping... {$DATA['pattern']} already exists.";
				}else{
					$this->wrap_add_object($DATA,$TYPE,$SITE);
				}
			}else{
				$this->wrap_add_object($DATA,$TYPE,$SITE);
			}

			$DATA = [
					"routePartitionName"			=> "PT_{$SITE}",
					"pattern"						=> "*{$PATTERN}",
					"calledPartyTransformationMask"	=> "*{$NPA}{$NXX}XXXX",
					"callingSearchSpaceName"		=> "CSS_{$SITE}",
					"description"					=> "{$SITE} voicemail pattern {$PATTERN}",
					"usage"							=> "Translation",
					];
			// Check if the object already exists. If it isn't then add it. 
			if(!empty($site_array[$TYPE])){
				if(in_array($DATA['pattern'],$site_array[$TYPE])){
					$this->results[$TYPE] = "Skipping... {$DATA['pattern']} already exists.";
				}else{
					$this->wrap_add_object($DATA,$TYPE,$SITE);
				}
			}else{
				$this->wrap_add_object($DATA,$TYPE,$SITE);
			}
		}
		
		if(isset($OPERATOR)){
			// Create Operator Translation Patterns.
			$DATA = [
					"routePartitionName"			=> "PT_{$SITE}",
					"pattern"						=> "0",
					"calledPartyTransformationMask"	=> "{$OPERATOR}",
					"callingSearchSpaceName"		=> "CSS_{$SITE}",
					"description"					=> "{$SITE} dial pattern Operator {$OPERATOR}",
					"usage"							=> "Translation",
					];
			// Check if the object already exists. If it isn't then add it. 
			if(!empty($site_array[$TYPE])){
				if(in_array($DATA['pattern'],$site_array[$TYPE])){
					$this->results[$TYPE] = "Skipping... {$DATA['pattern']} already exists.";
				}else{
					$this->wrap_add_object($DATA,$TYPE,$SITE);
				}
			}else{
				$this->wrap_add_object($DATA,$TYPE,$SITE);
			}

			$DATA = [
					"routePartitionName"			=> "PT_{$SITE}",
					"pattern"						=> "*0",
					"calledPartyTransformationMask"	=> "*{$OPERATOR}",
					"callingSearchSpaceName"		=> "CSS_{$SITE}",
					"description"					=> "{$SITE} dial pattern Operator Voicemail *{$OPERATOR}",
					"usage"							=> "Translation",
					];
			// Check if the object already exists. If it isn't then add it. 
			if(!empty($site_array[$TYPE])){
				if(in_array($DATA['pattern'],$site_array[$TYPE])){
					$this->results[$TYPE] = "Skipping... {$DATA['pattern']} already exists.";
				}else{
					$this->wrap_add_object($DATA,$TYPE,$SITE);
				}
			}else{
				$this->wrap_add_object($DATA,$TYPE,$SITE);
			}
		}
		
		// 17 - Create our 911 Route List

		// Calculated variables
		$TYPE = "RouteList";

        // Build Array of Route List
        $DATA = [
                    'name'                        => "RL_{$SITE}_911",
                    'description'                 => "{$SITE} - 911 Calling Route List",
                    'callManagerGroupName'        => "CMG-{$SITE}",
                    'routeListEnabled'            => true,
                    'runOnEveryNode'              => true,

                    'members'                    => [
                                                        'member' => [
                                                                    'routeGroupName'                         => "RG_{$SITE}",
                                                                    'selectionOrder'                         => 1,
                                                                    'useFullyQualifiedCallingPartyNumber'    => 'Default',
                                                                    ],
                                                    ],
                ];
		// Check if the object already exists. If it isn't then add it. 
		if(!empty($site_array[$TYPE])){
			if(in_array($DATA['name'],$site_array[$TYPE])){
				$this->results[$TYPE] = "Skipping... {$DATA['name']} already exists.";
			}else{
				$this->wrap_add_object($DATA,$TYPE,$SITE);
			}
		}else{
			$this->wrap_add_object($DATA,$TYPE,$SITE);
		}
		
		
		// 18 - Create our 911 Route Patterns

		// Calculated variables
		$TYPE = "RoutePattern";

        // Build Array of Route List		
		
		$PATTERNS = [
					[
						'pattern'                     => '911',
						'description'                 => "{$SITE} 911 - Emergency Services",
						'routePartitionName'          => "PT_{$SITE}_911",
						'blockEnable'                 => 'false',
						'useCallingPartyPhoneMask'    => 'Default',
						'networkLocation'             => 'OffNet',
						//"routeFilterName"			=> "",
						'patternUrgency'            => 'false',

						'destination'                    => [
															'routeListName' => "RL_{$SITE}_911",

														],
					],
					[
						'pattern'                     => '9.911',
						'description'                 => "{$SITE} 911 - Emergency Services",
						'routePartitionName'          => "PT_{$SITE}_911",
						'blockEnable'                 => 'false',
						'useCallingPartyPhoneMask'    => 'Default',
						'networkLocation'             => 'OffNet',
						//"routeFilterName"			=> "",
						'patternUrgency'            => 'false',

						'destination'                    => [
															'routeListName' => "RL_{$SITE}_911",

														],
					],
		];
		
		// Add each pattern in the array. 
		foreach($PATTERNS as $DATA){
			// Check if the object already exists. If it isn't then add it. 
			if(!empty($site_array[$TYPE])){
				if(in_array($DATA['pattern'],$site_array['RoutePattern'])){
					$this->results[$TYPE] = "Skipping... {$DATA['pattern']} already exists.";
					continue;
				}else{
					$this->wrap_add_object($DATA,$TYPE,$SITE);
				}
			}else{
				$this->wrap_add_object($DATA,$TYPE,$SITE);
			}
		}

		return $this->results;
	}
}
