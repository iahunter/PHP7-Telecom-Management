<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OwenIt\Auditing\Auditable;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Auth\AuthController;

class Ldap extends Controller
{
    //

    //use Helpers;
    public function __construct()
    {
        // Only authenticated users can make these calls
        $this->middleware('jwt.auth');

        // Create new Auth Controller for LDAP functions.
        $this->Auth = new AuthController();
    }

    public function listusers()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $result = $this->Auth->listusers();

        $response = [
            'status_code'    => 200,
            'success'        => true,
            'message'        => '',
            'result'         => $result,
            ];

        return response()->json($response);
    }

    public function user_update_ipphone(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $username = $request->username;
        $phonenumber = $request->ipphone;

        $result = $this->Auth->changeLdapPhone($username, $phonenumber);

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'result'         => $result,
                    ];

        return response()->json($response);
    }

    public function get_user(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $username = $request->username;
        //print $username;

        $result = $this->Auth->getUserLdapPhone($username);
		$fulluser = $result['user'];
		$fulluser = explode(',', $fulluser);
		foreach($fulluser as $value){
			if ($value == "OU=Disabled Users"){
				$result['disabled'] = true;
			}
			if (isset($result['disabled']) &&  $result['disabled'] != true){
				$result['disabled'] = false;
			}
		}
		//return $fulluser;
        //print_r($result);
        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'result'         => $result,
                    ];

        return response()->json($response);
    }
}
