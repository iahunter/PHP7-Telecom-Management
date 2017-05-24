<?php

namespace App\Http\Controllers;

use App\Ping;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class PingController extends Controller
{
    //
    public function pinghost(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions

        if (! $user->can('read', Ping::class)) {
            abort(401, 'You are not authorized');
        }

        return Ping::pinghost($request->host);
    }
}
