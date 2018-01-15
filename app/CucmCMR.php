<?php

namespace App;

use DB;
use Carbon\Carbon;
use phpseclib\Net\SFTP as Net_SFTP;
use Illuminate\Database\Eloquent\Model;

class CucmCMR extends Model
{
    protected $table = 'cucm_cmrs';
    protected $fillable = ['globalCallID_callId',
                            'dateTimeStamp',
                            'directoryNum',
                            'callIdentifier',
                            'directoryNumPartition',
                            'deviceName',
                            'varVQMetrics',

							'numberPacketsSent',
							'numberPacketsReceived',
							'jitter', 
							'numberPacketsLost',
							'packetLossPercent',

							'cmrraw',
                            'json',
                        ];

	// Cast data type conversions. Converting one type of data to another.
    protected $casts = [
			'cmrraw'  => 'array',
            'json' => 'array',
        ];
		
		
	public static function get_log_names()
    {
        $time = \Carbon\Carbon::now();

        $sftp = new Net_SFTP(env('CUCMCDR_SERVER'));
        if (! $sftp->login(env('CUCMCDR_USER'), env('CUCMCDR_PASS'))) {
            exit('Login Failed');
        }

        $sftp->chdir(env('CUCMCDR_DIR'));

        $fileobject = $sftp->rawlist();

        $files = (array) $fileobject;
        $fileobject = '';

        //print_r($files);

        $cdrregex = '/^cdr/';
		$cmrregex = '/^cmr/';

        $cdr_files = [];
		$cmr_files = [];
		
        foreach ($files as $file) {
            if (array_key_exists('filename', $file)) {
                $filename = $file['filename'];

                //return $type;
                if (preg_match($cdrregex, $filename)) {
                    //$cdr_files[] = $files[$filename];
                    $cdr_files[] = $file['filename'];
                    //print($filename).PHP_EOL;
                }elseif (preg_match($cmrregex, $filename)) {
                    //$cdr_files[] = $files[$filename];
                    $cmr_files[] = $file['filename'];
                    //print($filename).PHP_EOL;
                }
            } else {
                //print_r($file);
            }
        }
        $files = [];

        $locations = [];
        foreach ($cdr_files as $file) {
            $locations['cdrs'][] = env('CUCMCDR_DIR').$file;
        }
		foreach ($cmr_files as $file) {
            $locations['cmrs'][] = env('CUCMCDR_DIR').$file;
        }

        //print_r($locations);
        return $locations;
    }
	
	// Use this function instead of statically mapping
    public static function cmr_key_map_to_headers($header, $callrecord)
    {
        foreach ($header as $key => $value) {
            $header[$key] = trim($value, '"');
        }
        foreach ($callrecord as $key => $value) {
            $callrecord[$key] = trim($value, '"');
        }
        //var_dump($header);
        return array_combine($header, $callrecord);
    }
	
	public static function timedateformat($time)
    {
        // This returns Y-m-d format.
        $date = Carbon::createFromTimestamp($time)->toDateTimeString();

        return $date;
    }
}
