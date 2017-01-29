<?php

namespace App\Http\Controllers;

use App\Sonus5k;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use phpseclib\Net\SFTP as Net_SFTP;

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

        $CALLS = [];
        foreach ($this->SBCS as $SBC) {
            $CALLS[$SBC] = Sonus5k::listactivecalls($SBC);
        }

        return $CALLS;
    }

    public function listactivealarms(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check user permissions
        if (! $user->can('read', Sonus5k::class)) {
            abort(401, 'You are not authorized');
        }

        $CALLS = [];
        foreach ($this->SBCS as $SBC) {
            $CALLS[$SBC] = Sonus5k::listactivealarms($SBC);
        }

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
