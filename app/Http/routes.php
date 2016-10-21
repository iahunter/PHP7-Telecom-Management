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

/* Default Route
Route::get('/', function () {
    return view('welcome');
});
*/

Route::get('/', function () {
    return redirect('ui');
});

$api->version('v1', function ($api) {
    $api->get('hello', function () {
        return "Hello world - demo app!\n";
    });

    /**
     * @SWG\Info(title="Phone Number API", version="0.1")
     **/


    // Authenticate returns a JWT upon success to authenticate additional API calls.

    /**
     * @SWG\Get(
     *     path="/telephony/api/authenticate",
     *     tags={"Authentication"},
     *     summary="Get JSON web token by TLS client certificate authentication",
     *     @SWG\Response(
     *         response=200,
     *         description="Authentication succeeded",
     *         ),
     *     ),
     * )
     **/
    $api->get('authenticate', 'App\Http\Controllers\Auth\AuthController@authenticate');

    /**
     * @SWG\Post(
     *     path="/telephony/api/authenticate",
     *     tags={"Authentication"},
     *     summary="Get JSON web token by LDAP user authentication",
     *     @SWG\Parameter(
     *         name="username",
     *         in="formData",
     *         description="LDAP username",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         in="formData",
     *         description="LDAP password",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Authentication succeeded",
     *         ),
     *     ),
     * )
     **/
    $api->post('authenticate', 'App\Http\Controllers\Auth\AuthController@authenticate');


    // Disallow users to list users and get userinfo from API.
    //$api->get('listusers', 'App\Http\Controllers\Auth\AuthController@listusers');

    // Get your user info.
    $api->get('userinfo', 'App\Http\Controllers\Auth\AuthController@userinfo');

	
	// Did and Didblock routes
    require __DIR__.'/did.routes.php';

	// Did and Didblock routes
    require __DIR__.'/cucm.routes.php';
	
});

Route::auth();
