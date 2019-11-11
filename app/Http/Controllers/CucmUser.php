<?php

namespace App\Http\Controllers;

use App\Cucmclass;	// Cache
// Add Dummy CUCM class for permissions use for now.
use App\PhoneMACD;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
// Include the JWT Facades shortcut
use Tymon\JWTAuth\Facades\JWTAuth;

class CucmUser extends Cucm
{
    public function getUserbyUsername(Request $request, $username)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cucmclass::class)) {
            if (! $user->can('read', PhoneMACD::class)) {
                abort(401, 'You are not authorized');
            }
        }

        $user = Cucmclass::get_user_by_userid($username);

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $user,
                    ];

        return response()->json($response);
    }

    // Create New Phone
    public function createUser(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check user permissions
        if (! $user->can('create', Cucmclass::class)) {
            if (! $user->can('create', PhoneMACD::class)) {
                abort(401, 'You are not authorized');
            }
        }

        //return $request->all();

        $errors = [];

        $data = [];
        // Check if firstname is Set
        if (! isset($request->firstname) || ! $request->firstname) {
            $errors[] = 'Error, no firstname set';
        }
        $data['firstname'] = $request->firstname;

        // Check if lastname is Set
        if (! isset($request->lastname) || ! $request->lastname) {
            $errors[] = 'Error, no lastname set';
        }
        $data['lastname'] = $request->lastname;

        // Check if username is Set
        if (! isset($request->username) || ! $request->username) {
            $errors[] = 'Error, no username set';
        }
        $data['username'] = $request->username;

        if (isset($request->dn) && $request->dn) {
            $data['dn'] = $request->dn;
        }

        $user = Cucmclass::add_user($data);

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $user,
                    ];

        return response()->json($response);
    }
}
