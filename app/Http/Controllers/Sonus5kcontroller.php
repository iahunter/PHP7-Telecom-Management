<?php

namespace App\Http\Controllers;

use App\Sonus5k;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;


class Sonus5kcontroller extends Controller
{
	//use Helpers;
    public function __construct()
    {
        // Only authenticated users can make these calls
        $this->middleware('jwt.auth');
    }
	
    public function listactivecalls(Request $request)
    {
		$user = JWTAuth::parseToken()->authenticate();
		
        return Sonus5k::listactivecalls();
    }
}
