<?php

namespace App\Http\Controllers;

use DB;
use App\West911EnableEGW;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class West911EnableEGWController extends Controller
{
    public function __construct()
    {
        // Only authenticated users can make these calls
        $this->middleware('jwt.auth');

        // Construct new cucm object
        $this->DB = DB::connection('egw');
    }

    public function get_all_cisco_phones(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        //$result = $this->DB->select('select * from cisco_phone');
        $result = West911EnableEGW::get_egw_phones();

        $response = [
                    'status_code'       => 200,
                    'success'           => true,
                    'message'           => '',
                    'result'            => $result,
                    ];

        return response()->json($response);
    }

    public function get_cisco_phone_by_name(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $name = $request->name;

        $result = West911EnableEGW::get_cisco_phone_by_name($name);

        $response = [
                    'status_code'       => 200,
                    'success'           => true,
                    'message'           => '',
                    'result'            => $result,
                    ];

        return response()->json($response);
    }

    public function get_all_endpoints(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $result = West911EnableEGW::get_all_endpoints();

        $response = [
                    'status_code'       => 200,
                    'success'           => true,
                    'message'           => '',
                    'result'            => $result,
                    ];

        return response()->json($response);
    }

    public function get_endpoint_by_name(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $name = $request->name;

        $result = West911EnableEGW::get_endpoint_by_name($name);

        $response = [
                    'status_code'       => 200,
                    'success'           => true,
                    'message'           => '',
                    'result'            => $result,
                    ];

        return response()->json($response);
    }

    public function get_all_endpoints_ip_erl(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $result = West911EnableEGW::get_all_endpoints_ip_erl();

        $response = [
                    'status_code'       => 200,
                    'success'           => true,
                    'message'           => '',
                    'result'            => $result,
                    ];

        return response()->json($response);
    }

    public function list_erls(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $result = West911EnableEGW::list_erls();

        $response = [
                    'status_code'       => 200,
                    'success'           => true,
                    'message'           => '',
                    'result'            => $result,
                    ];

        return response()->json($response);
    }

    public function list_erls_and_phone_counts(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $result = West911EnableEGW::list_erls_and_phone_counts();

        $response = [
                    'status_code'       => 200,
                    'success'           => true,
                    'message'           => '',
                    'result'            => $result,
                    ];

        return response()->json($response);
    }
}
