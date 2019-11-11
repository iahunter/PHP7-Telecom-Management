<?php

namespace App\Http\Controllers;

use App\Cucmclass;
use App\Http\Controllers\Auth\AuthController;
use App\PhoneMACD;
use Illuminate\Http\Request;
use OwenIt\Auditing\Auditable;
use Tymon\JWTAuth\Facades\JWTAuth;

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
        if (! $user->can('read', PhoneMACD::class)) {
            if (! $user->can('read', Cucmclass::class)) {
                abort(401, 'You are not authorized');
            }
        }

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
        if (! $user->can('update', PhoneMACD::class)) {
            if (! $user->can('update', Cucmclass::class)) {
                abort(401, 'You are not authorized');
            }
        }

        $username = $request->username;
        $phonenumber = $request->ipphone;

        $username = trim($username);

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
        if (! $user->can('read', PhoneMACD::class)) {
            if (! $user->can('read', Cucmclass::class)) {
                abort(401, 'You are not authorized');
            }
        }

        $username = $request->username;
        //print $username;

        $result = $this->Auth->getUserLdapPhone($username);
        $fulluser = $result['user'];
        $fulluser = explode(',', $fulluser);
        foreach ($fulluser as $value) {
            if ($value == 'OU=Disabled Users') {
                $result['disabled'] = true;
            }
            if (isset($result['disabled']) && $result['disabled'] != true) {
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
