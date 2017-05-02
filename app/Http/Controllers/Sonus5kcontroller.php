<?php

namespace App\Http\Controllers;

use App\Sonus5k;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use phpseclib\Net\SFTP as Net_SFTP;
use Illuminate\Support\Facades\Cache;

class Sonus5kcontroller extends Controller
{
    //use Helpers;

    public $SBCS;

    public function __construct()
    {
        // Only authenticated users can make these calls
        $this->middleware('jwt.auth');

        // Populate SBC list
        $this->SBCS = [
                        env('SONUS1'),
                        env('SONUS2'),
                        ];
    }

    public function listactivecalls(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Sonus5k::class)) {
            abort(401, 'You are not authorized');
        }

        // Name of Cache key.
        $key = 'listactivecalls';

        // Check if calls exist in cache. If not then move on.
        if (Cache::has($key)) {
            //Log::info(__METHOD__.' Used Cache');
            return Cache::get($key);
        }
        //Log::info(__METHOD__.' Did not Use Cache');
        $CALLS = [];
        foreach ($this->SBCS as $SBC) {
            $CALLS[$SBC] = Sonus5k::listactivecalls($SBC);
        }

        // Cache Calls for 5 seconds - Put the $CALLS as value of cache.
        $time = Carbon::now()->addSeconds(7);
        Cache::put($key, $CALLS, $time);

        return $CALLS;
    }
	
	public function listcallDetailStatus(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Sonus5k::class)) {
            abort(401, 'You are not authorized');
        }

        // Name of Cache key.
        $key = 'listcallDetailStatus';

        // Check if calls exist in cache. If not then move on.
        if (Cache::has($key)) {
            //Log::info(__METHOD__.' Used Cache');
            return Cache::get($key);
        }
        //Log::info(__METHOD__.' Did not Use Cache');
        $CALLS = [];
        foreach ($this->SBCS as $SBC) {
            $CALLS[$SBC] = Sonus5k::listcallDetailStatus($SBC);
        }

        // Cache Calls for 5 seconds - Put the $CALLS as value of cache.
        $time = Carbon::now()->addSeconds(7);
        Cache::put($key, $CALLS, $time);

        return $CALLS;
    }
	
	public function indexAssocByKey($stuff, $key)
	{
		$result = [];
		foreach($stuff as $thing) {
			$index = $thing[$key];
			if (!$index) {
				throw new \Exception('error sorting arrya by key, key was missing from thing');
			}
			$result[$index] = $thing;
		}
		return $result;
	}
	
	public function listcallDetailStatus_Media(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Sonus5k::class)) {
            abort(401, 'You are not authorized');
        }

        // Name of Cache key.
        $key = 'listcallDetailStatus_Media';

        // Check if calls exist in cache. If not then move on.
        if (Cache::has($key) && !$request->get('nocache')) {
            //Log::info(__METHOD__.' Used Cache');
            return Cache::get($key);
        }
		
        //Log::info(__METHOD__.' Did not Use Cache');
        $RETURN = [];
        foreach ($this->SBCS as $SBC) {
			$callsMedia = [];
            $calls = Sonus5k::listcallDetailStatus($SBC);
			$media = Sonus5k::listcallMediaStatus($SBC);
			
			$calls = (array) $calls['sonusActiveCall:callDetailStatus'];
			$media = (array) $media['sonusActiveCall:callMediaStatus'];
			
			$indexedCalls = $this->indexAssocByKey($calls, 'GCID');
			$indexedMedia = $this->indexAssocByKey($media, 'GCID');
			//return $indexedMedia;
			foreach($indexedCalls as $key => $value){
				
				// Merge our Call Details and our Media Details into a single array for each GCID. 
				$callsMedia[$key] = array_merge($indexedCalls[$key], $indexedMedia[$key]);

			}
			// Add the original key back on so the UI doesn't get jacked up. 
			$RETURN[$SBC]['sonusActiveCall:callSummaryStatus'] = $callsMedia;
        }

        // Cache Calls for 15 seconds - Put the $CALLS as value of cache.
        $time = Carbon::now()->addSeconds(15);
        Cache::put($key, $RETURN, $time);

        return $RETURN;
    }
	
	public function listcallMediaStatus(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Sonus5k::class)) {
            abort(401, 'You are not authorized');
        }

        // Name of Cache key.
        $key = 'listcallMediaStatus';

        // Check if calls exist in cache. If not then move on.
        if (Cache::has($key)) {
            //Log::info(__METHOD__.' Used Cache');
            return Cache::get($key);
        }
        //Log::info(__METHOD__.' Did not Use Cache');
        $CALLS = [];
        foreach ($this->SBCS as $SBC) {
            $CALLS[$SBC] = Sonus5k::listcallMediaStatus($SBC);
        }

        // Cache Calls for 5 seconds - Put the $CALLS as value of cache.
        $time = Carbon::now()->addSeconds(5);
        Cache::put($key, $CALLS, $time);

        return $CALLS;
    }

    public function listactivealarms(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check user permissions
        if (! $user->can('read', Sonus5k::class)) {
            abort(401, 'You are not authorized');
        }

        // Name of Cache key.
        $key = 'listactivealarms';

        // Check if calls exist in cache. If not then move on.
        if (Cache::has($key)) {
            //Log::info(__METHOD__.' Used Cache');
            return Cache::get($key);
        }

        $CALLS = [];
        foreach ($this->SBCS as $SBC) {
            $CALLS[$SBC] = Sonus5k::listactivealarms($SBC);
        }

        // Cache Calls for 5 seconds - Put the $CALLS as value of cache.
        $time = Carbon::now()->addSeconds(60);
        Cache::put($key, $CALLS, $time);

        return $CALLS;
    }

    public function compareconfigs()
    {
        die();
        $user = JWTAuth::parseToken()->authenticate();

        // Check user permissions
        if (! $user->can('read', Sonus5k::class)) {
            abort(401, 'You are not authorized');
        }

        $CALLS = [];
        foreach ($this->SBCS as $SBC) {
            $backup = Sonus5k::configbackup($SBC);
            //print_r($backup);
            $location = $backup['output']['reason'];
            $location = explode('Configuration Saved as ', $location);
            //return $location;
            $location = $location[1];

            // Download backup
            $server = $SBC;
            $file = $this->sftpdownload($server, $location);
            print_r($file);

            print_r(Sonus5k::removeconfigbackup($SBC, $location));

        //return Sonus5k::listactivecalls();
        }
    }

    public function sftpdownload($server, $location)
    {
        $localdir = __DIR__.'/tmp';
        $files = scandir($localdir);
        print_r($files);

        $sftp = new Net_SFTP($server.':2024');
        if (! $sftp->login(env('SONUSSFTPUSER'), env('SONUSSFTPPASS'))) {
            exit('Login Failed');
        }

        // outputs the contents of filename.remote to the screen
        //echo $sftp->get($location);
        // copies filename.remote to filename.local from the SFTP server
        $sftp->get($location, "{$localdir}/{$server}");

        $files = scandir($localdir);
        print_r($files);
    }
}
