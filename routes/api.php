<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$api = app('Dingo\Api\Routing\Router');



$api->version('v1', function ($api) {
	 
    $api->get('hello', function () {
        return "Hello world - demo app!\n";
    });
	
	/**
     * @SWG\Info(title="Telephony Management API", version="1.0")
     **/


    // Disallow users to list users and get userinfo from API.
    //$api->get('listusers', 'App\Http\Controllers\Auth\AuthController@listusers');

    // Get your user info.
    $api->get('userinfo', 'App\Http\Controllers\Auth\AuthController@userinfo');

	// Auth routes
    require __DIR__.'/api.auth.php';
	
	// Did and Didblock routes
    require __DIR__.'/api.did.php';

	// CUCM routes
    require __DIR__.'/api.cucm.php';
	
});

