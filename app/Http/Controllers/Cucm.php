<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
// Include the JWT Facades shortcut
use Tymon\JWTAuth\Facades\JWTAuth;

class Cucm extends Controller
{
    public function __construct()
    {
        // Only authenticated users can make these calls
        $this->middleware('jwt.auth');
		
		// Construct new cucm object
        $this->cucm = new \CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
                                                    storage_path(env('CALLMANAGER_WSDL')),
                                                    env('CALLMANAGER_USER'),
                                                    env('CALLMANAGER_PASS')
                                                    );

    }
	
	public function listsites()
    {
        //$user = JWTAuth::parseToken()->authenticate();

        try {
            $sites = $this->cucm->get_site_names();
            //$sites = ["KHONEOMA"];

            if (! count($sites)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
			
        } catch (\Exception $e) {
            echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }
		
		/*
        $show = [];
        
		foreach ($didblocks as $didblock) {
            if ($user->can('read', $didblock)) {
                $didblock->stats = $stats[$didblock->id];
                $show[] = $didblock;
            }
        }
		*/
        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'      => $sites,
                    ];

        return response()->json($response);
    }
	
	
}