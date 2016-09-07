<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

$api = app('Dingo\Api\Routing\Router');

Route::get('/', function () {
    return view('welcome');
});

$api->version('v1', function ($api) {
    $api->get('hello', function () {
        return "Hello world - demo app!\n";
    });

	/**
     * @SWG\Info(title="Phone Number API", version="0.1")
     **/
	
    // This spits back a JWT to authenticate additional API calls.
    $api->get('authenticate', 'App\Http\Controllers\Auth\AuthController@authenticate');
    $api->post('authenticate', 'App\Http\Controllers\Auth\AuthController@authenticate');
    $api->get('listusers', 'App\Http\Controllers\Auth\AuthController@listusers');
    $api->get('userinfo', 'App\Http\Controllers\Auth\AuthController@userinfo');


    // DID Block App routes
    $api->post('didblock', 'App\Http\Controllers\Didcontroller@createDidblock');
	
	/**
		 * @SWG\Get(
		 *     path="/telephony/api/didblock",
		 *     tags={"Get Did Block"},
		 *     summary="List of DID Blocks for authorized user",
		 *     description="",
		 *     operationId="getDidblock",
		 *     consumes={"application/json"},
		 *     produces={"application/json"},
		 *     @SWG\Response(
		 *         response=200,
		 *         description="successful operation",
		 *     )
		 * )
		 */
    $api->get('didblock/{id}', 'App\Http\Controllers\Didcontroller@getDidblock');
    $api->get('didblock', 'App\Http\Controllers\Didcontroller@listDidblock');
	
	
    $api->put('didblock/{id}', 'App\Http\Controllers\Didcontroller@updateDidblock');
    $api->delete('didblock/{id}', 'App\Http\Controllers\Didcontroller@deleteDidblock');

    // List DIDs by block id
    $api->get('didblock/{id}/dids', 'App\Http\Controllers\Didcontroller@listDidbyBlockID');


    // DID App routes
    // $api->post('did', 'App\Http\Controllers\Didcontroller@createDid'); // Individual DID creation not allowed.
    $api->get('did/{id}', 'App\Http\Controllers\Didcontroller@getDid');
    $api->get('did', 'App\Http\Controllers\Didcontroller@listDid');
    $api->put('did/{id}', 'App\Http\Controllers\Didcontroller@updateDid');
    // $api->delete('did/{id}', 'App\Http\Controllers\Didcontroller@deleteDid'); // Individual DID deletion Not allowed.
});

Route::auth();
