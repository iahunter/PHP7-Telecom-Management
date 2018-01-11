<?php

namespace App;

use DB;
use Carbon\Carbon;
use phpseclib\Net\SFTP as Net_SFTP;

use Illuminate\Database\Eloquent\Model;

class CucmCDR extends Model
{
    protected $table = 'cucm_cdrs';
    protected $fillable = ['globalCallID_callId',
                            'dateTimeConnect',
                            'dateTimeDisconnect',
                            'duration',
                            'callingPartyNumber',
                            'originalCalledPartyNumber',
                            'finalCalledPartyNumber',
                            'origDeviceName',
                            'destDeviceName',
                            'origIpv4v6Addr',
                            'destIpv4v6Addr',
                            'originalCalledPartyPattern',
                            'finalCalledPartyPattern',
                            'lastRedirectingPartyPattern',

                            'raw',
							'json',
                        ];

    // Cast data type conversions. Converting one type of data to another.
    protected $casts = [
            'raw' => 'array',
			'json' => 'array',
        ];

	
	public static function get_cdr_log_names()
    {
        $time = \Carbon\Carbon::now();

        $sftp = new Net_SFTP(env('CUCMCDR_SERVER'));
        if (! $sftp->login(env('CUCMCDR_USER'), env('CUCMCDR_PASS'))) {
            exit('Login Failed');
        }

        $sftp->chdir('/home/telecom/CDR/');

        $fileobject = $sftp->rawlist();

        $files = (array) $fileobject;
        $fileobject = '';
		
		//print_r($files);

        $regex = '/^cdr/';

        $cdr_files = [];

        foreach ($files as $file) {
			
			if(array_key_exists('filename', $file)){
				$filename = $file['filename'];
				
				//return $type;
				if (preg_match($regex, $filename)) {
					//$cdr_files[] = $files[$filename];
					$cdr_files[] = $file['filename'];
					//print($filename).PHP_EOL;
				}
			}else{
				//print_r($file);
			}
            
        }
        $files = [];
		
		//print_r($cdr_files);
		/*
        $cdr_files_array = $cdr_files;
        $cdr_files = [];
        foreach ($cdr_files_array as $file) {
            $cdr_files[$file['atime']] = $file;
        }

        krsort($cdr_files);
        //print_r($cdr_files);
		*/
        $locations = [];
        foreach ($cdr_files as $file) {
            $locations[] = "/home/telecom/CDR/{$file}";
        }

		//print_r($locations);
        return $locations;
    }
	
	public static function get_cmr_log_names()
    {
        $time = \Carbon\Carbon::now();

        $sftp = new Net_SFTP(env('CUCMCDR_SERVER'), 22);
        if (! $sftp->login(env('CUCMCDR_USER'), env('CUCMCDR_PASS'))) {
            exit('Login Failed');
        }

        $sftp->chdir('/home/telecom/CDR/');

        $fileobject = $sftp->rawlist();

        $files = (array) $fileobject;
        $fileobject = '';

        $regex = '/cmr$/';

        $cdr_files = [];

        foreach ($files as $file) {
            $filename = $file['filename'];

            $type = explode('.', $filename);
            //return $type;
            if (isset($type[1]) && $type[1] == 'ACT') {
                $cdr_files[] = $files[$filename];
            }
        }
        $files = [];

        $cdr_files_array = $cdr_files;
        $cdr_files = [];
        foreach ($cdr_files_array as $file) {
            $cdr_files[$file['atime']] = $file;
        }

        krsort($cdr_files);
        //print_r($cdr_files);

        $locations = [];
        foreach ($cdr_files as $file) {
            $locations[] = "/home/telecom/CDR/{$file['filename']}";
        }

        return $locations;
    }
	
	
	
	// Use this function instead of statically mapping
	public static function cdr_key_map_to_headers($header, $callrecord)
	{
		foreach($header as $key => $value){
			$header[$key] = trim($value, '"'); 
		}
		foreach($callrecord as $key => $value){
			$callrecord[$key] = trim($value, '"'); 
		}
		//var_dump($header); 
		return array_combine($header, $callrecord); 
	}
	
	
	/*
	// Depricated.
	public static function cdr_key_map_to_headers($header, $callrecord)
	{
		// Converts each record into an associative array and maps to header keys. 
		$newcallrecord = []; 
		foreach($callrecord as $key => $value){
			$newcallrecord[$header[$key]] = $callrecord[$key]; 
		}
		return $newcallrecord; 
	}
	*/

	
	// Depricated. 
	public static function cdr_key_map($callrecord)
    {
		$newrecord = []; 

		$newrecord["cdrRecordType"] = $callrecord[0];
		$newrecord["globalCallID_callManagerId"] = $callrecord[1];
		$newrecord["globalCallID_callId"] = $callrecord[2];
		$newrecord["origLegCallIdentifier"] = $callrecord[3];
		$newrecord["dateTimeOrigination"] = $callrecord[4];
		$newrecord["origNodeId"] = $callrecord[5];
		$newrecord["origSpan"] = $callrecord[6];
		$newrecord["origIpAddr"] = $callrecord[7];
		$newrecord["callingPartyNumber"] = $callrecord[8];
		$newrecord["callingPartyUnicodeLoginUserID"] = $callrecord[9];
		$newrecord["origCause_location"] = $callrecord[10];
		$newrecord["origCause_value"] = $callrecord[11];
		$newrecord["origPrecedenceLevel"] = $callrecord[12];
		$newrecord["origMediaTransportAddress_IP"] = $callrecord[13];
		$newrecord["origMediaTransportAddress_Port"] = $callrecord[14];
		$newrecord["origMediaCap_payloadCapability"] = $callrecord[15];
		$newrecord["origMediaCap_maxFramesPerPacket"] = $callrecord[16];
		$newrecord["origMediaCap_g723BitRate"] = $callrecord[17];
		$newrecord["origVideoCap_Codec"] = $callrecord[18];
		$newrecord["origVideoCap_Bandwidth"] = $callrecord[19];
		$newrecord["origVideoCap_Resolution"] = $callrecord[20];
		$newrecord["origVideoTransportAddress_IP"] = $callrecord[21];
		$newrecord["origVideoTransportAddress_Port"] = $callrecord[22];
		$newrecord["origRSVPAudioStat"] = $callrecord[23];
		$newrecord["origRSVPVideoStat"] = $callrecord[24];
		$newrecord["destLegIdentifier"] = $callrecord[25];
		$newrecord["destNodeId"] = $callrecord[26];
		$newrecord["destSpan"] = $callrecord[27];
		$newrecord["destIpAddr"] = $callrecord[28];
		$newrecord["originalCalledPartyNumber"] = $callrecord[29];
		$newrecord["finalCalledPartyNumber"] = $callrecord[30];
		$newrecord["finalCalledPartyUnicodeLoginUserID"] = $callrecord[31];
		$newrecord["destCause_location"] = $callrecord[32];
		$newrecord["destCause_value"] = $callrecord[33];
		$newrecord["destPrecedenceLevel"] = $callrecord[34];
		$newrecord["destMediaTransportAddress_IP"] = $callrecord[35];
		$newrecord["destMediaTransportAddress_Port"] = $callrecord[36];
		$newrecord["destMediaCap_payloadCapability"] = $callrecord[37];
		$newrecord["destMediaCap_maxFramesPerPacket"] = $callrecord[38];
		$newrecord["destMediaCap_g723BitRate"] = $callrecord[39];
		$newrecord["destVideoCap_Codec"] = $callrecord[40];
		$newrecord["destVideoCap_Bandwidth"] = $callrecord[41];
		$newrecord["destVideoCap_Resolution"] = $callrecord[42];
		$newrecord["destVideoTransportAddress_IP"] = $callrecord[43];
		$newrecord["destVideoTransportAddress_Port"] = $callrecord[44];
		$newrecord["destRSVPAudioStat"] = $callrecord[45];
		$newrecord["destRSVPVideoStat"] = $callrecord[46];
		$newrecord["dateTimeConnect"] = $callrecord[47];
		$newrecord["dateTimeDisconnect"] = $callrecord[48];
		$newrecord["lastRedirectDn"] = $callrecord[49];
		$newrecord["pkid"] = $callrecord[50];
		$newrecord["originalCalledPartyNumberPartition"] = $callrecord[51];
		$newrecord["callingPartyNumberPartition"] = $callrecord[52];
		$newrecord["finalCalledPartyNumberPartition"] = $callrecord[53];
		$newrecord["lastRedirectDnPartition"] = $callrecord[54];
		$newrecord["duration"] = $callrecord[55];
		$newrecord["origDeviceName"] = $callrecord[56];
		$newrecord["destDeviceName"] = $callrecord[57];
		$newrecord["origCallTerminationOnBehalfOf"] = $callrecord[58];
		$newrecord["destCallTerminationOnBehalfOf"] = $callrecord[59];
		$newrecord["origCalledPartyRedirectOnBehalfOf"] = $callrecord[60];
		$newrecord["lastRedirectRedirectOnBehalfOf"] = $callrecord[61];
		$newrecord["origCalledPartyRedirectReason"] = $callrecord[62];
		$newrecord["lastRedirectRedirectReason"] = $callrecord[63];
		$newrecord["destConversationId"] = $callrecord[64];
		$newrecord["globalCallId_ClusterID"] = $callrecord[65];
		$newrecord["joinOnBehalfOf"] = $callrecord[66];
		$newrecord["comment"] = $callrecord[67];
		$newrecord["authCodeDescription"] = $callrecord[68];
		$newrecord["authorizationLevel"] = $callrecord[69];
		$newrecord["clientMatterCode"] = $callrecord[70];
		$newrecord["origDTMFMethod"] = $callrecord[71];
		$newrecord["destDTMFMethod"] = $callrecord[72];
		$newrecord["callSecuredStatus"] = $callrecord[73];
		$newrecord["origConversationId"] = $callrecord[74];
		$newrecord["origMediaCap_Bandwidth"] = $callrecord[75];
		$newrecord["destMediaCap_Bandwidth"] = $callrecord[76];
		$newrecord["authorizationCodeValue"] = $callrecord[77];
		$newrecord["outpulsedCallingPartyNumber"] = $callrecord[78];
		$newrecord["outpulsedCalledPartyNumber"] = $callrecord[79];
		$newrecord["origIpv4v6Addr"] = $callrecord[80];
		$newrecord["destIpv4v6Addr"] = $callrecord[81];
		$newrecord["origVideoCap_Codec_Channel2"] = $callrecord[82];
		$newrecord["origVideoCap_Bandwidth_Channel2"] = $callrecord[83];
		$newrecord["origVideoCap_Resolution_Channel2"] = $callrecord[84];
		$newrecord["origVideoTransportAddress_IP_Channel2"] = $callrecord[85];
		$newrecord["origVideoTransportAddress_Port_Channel2"] = $callrecord[86];
		$newrecord["origVideoChannel_Role_Channel2"] = $callrecord[87];
		$newrecord["destVideoCap_Codec_Channel2"] = $callrecord[88];
		$newrecord["destVideoCap_Bandwidth_Channel2"] = $callrecord[89];
		$newrecord["destVideoCap_Resolution_Channel2"] = $callrecord[90];
		$newrecord["destVideoTransportAddress_IP_Channel2"] = $callrecord[91];
		$newrecord["destVideoTransportAddress_Port_Channel2"] = $callrecord[92];
		$newrecord["destVideoChannel_Role_Channel2"] = $callrecord[93];
		$newrecord["IncomingProtocolID"] = $callrecord[94];
		$newrecord["IncomingProtocolCallRef"] = $callrecord[95];
		$newrecord["OutgoingProtocolID"] = $callrecord[96];
		$newrecord["OutgoingProtocolCallRef"] = $callrecord[97];
		$newrecord["currentRoutingReason"] = $callrecord[98];
		$newrecord["origRoutingReason"] = $callrecord[99];
		$newrecord["lastRedirectingRoutingReason"] = $callrecord[100];
		$newrecord["huntPilotPartition"] = $callrecord[101];
		$newrecord["huntPilotDN"] = $callrecord[102];
		$newrecord["calledPartyPatternUsage"] = $callrecord[103];
		$newrecord["IncomingICID"] = $callrecord[104];
		$newrecord["IncomingOrigIOI"] = $callrecord[105];
		$newrecord["IncomingTermIOI"] = $callrecord[106];
		$newrecord["OutgoingICID"] = $callrecord[107];
		$newrecord["OutgoingOrigIOI"] = $callrecord[108];
		$newrecord["OutgoingTermIOI"] = $callrecord[109];
		$newrecord["outpulsedOriginalCalledPartyNumber"] = $callrecord[110];
		$newrecord["outpulsedLastRedirectingNumber"] = $callrecord[111];
		$newrecord["wasCallQueued"] = $callrecord[112];
		$newrecord["totalWaitTimeInQueue"] = $callrecord[113];
		$newrecord["callingPartyNumber_uri"] = $callrecord[114];
		$newrecord["originalCalledPartyNumber_uri"] = $callrecord[115];
		$newrecord["finalCalledPartyNumber_uri"] = $callrecord[116];
		$newrecord["lastRedirectDn_uri"] = $callrecord[117];
		$newrecord["mobileCallingPartyNumber"] = $callrecord[118];
		$newrecord["finalMobileCalledPartyNumber"] = $callrecord[119];
		$newrecord["origMobileDeviceName"] = $callrecord[120];
		$newrecord["destMobileDeviceName"] = $callrecord[121];
		$newrecord["origMobileCallDuration"] = $callrecord[122];
		$newrecord["destMobileCallDuration"] = $callrecord[123];
		$newrecord["mobileCallType"] = $callrecord[124];
		$newrecord["originalCalledPartyPattern"] = $callrecord[125];
		$newrecord["finalCalledPartyPattern"] = $callrecord[126];
		$newrecord["lastRedirectingPartyPattern"] = $callrecord[127];
		$newrecord["huntPilotPattern"] = $callrecord[128];

		
        return $newrecord;
    }
	
	
	
	/*
	// Depricated. 
	public static function cmr_key_map($callrecord)
    {
		$newrecord = []; 
		
		$newrecord['cdrRecordType'] = $callrecord[0];
		$newrecord['globalCallID_callManagerId'] = $callrecord[1];
		$newrecord['globalCallId_callId'] = $callrecord[2];
		$newrecord['nodeId'] = $callrecord[3];
		$newrecord['directoryNumber'] = $callrecord[4];
		$newrecord['callIdentifier'] = $callrecord[5];
		$newrecord['dateTimeStamp'] = $callrecord[6];
		$newrecord['numberPacketsSent'] = $callrecord[7];
		$newrecord['numberOctetsSent'] = $callrecord[8];
		$newrecord['numberPacketsReceived'] = $callrecord[9];
		$newrecord['numberOctetsReceived'] = $callrecord[10];
		$newrecord['numberPacketsLost'] = $callrecord[11];
		$newrecord['jitter'] = $callrecord[12];
		$newrecord['latency'] = $callrecord[13];
		$newrecord['pkid'] = $callrecord[14];
		$newrecord['directoryNumberPartition'] = $callrecord[15];
		$newrecord['globalCallId_ClusterId'] = $callrecord[16];
		$newrecord['deviceName'] = $callrecord[17];
		$newrecord['varVQMetrics'] = $callrecord[18];
		$newrecord['duration'] = $callrecord[19];
		$newrecord['videoContentType'] = $callrecord[20];
		
        return $newrecord;
    }
	
	*/
	
	
	
}
