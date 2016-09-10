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

	 
    // Authenticate returns a JWT upon success to authenticate additional API calls.
	
	/**
     * @SWG\Get(
     *     path="/telephony/api/authenticate",
     *     tags={"Authenticate"},
     *     summary="Get JSON web token by TLS client certificate authentication",
     *     description="",
     *     operationId="authenticate",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->get('authenticate', 'App\Http\Controllers\Auth\AuthController@authenticate');
	
	/**
     * @SWG\Post(
     *     path="/telephony/api/authenticate",
     *     tags={"Authenticate"},
     *     summary="Get JSON web token by LDAP user authentication",
     *     description="",
     *     operationId="authenticate",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->post('authenticate', 'App\Http\Controllers\Auth\AuthController@authenticate');
	
	
	// Disallow users to list users and get userinfo from API. 
    //$api->get('listusers', 'App\Http\Controllers\Auth\AuthController@listusers');
    //$api->get('userinfo', 'App\Http\Controllers\Auth\AuthController@userinfo');


	/********************************
		DID Block App routes
	********************************/
    /**
     * @SWG\Post(
     *     path="/telephony/api/didblock",
     *     tags={"Did Block"},
     *     summary="Create DID Block",
     *     description="",
     *     operationId="createDidblock",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->post('didblock', 'App\Http\Controllers\Didcontroller@createDidblock');

    /**
     * @SWG\Get(
     *     path="/telephony/api/didblock{id}",
     *     tags={"Did Block"},
     *     summary="Get DID Block by ID",
     *     description="",
     *     operationId="getDidblock",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->get('didblock/{id}', 'App\Http\Controllers\Didcontroller@getDidblock');


    /**
     * @SWG\Get(
     *     path="/telephony/api/didblock",
     *     tags={"Did Block"},
     *     summary="List of DID Blocks for authorized user",
     *     description="",
     *     operationId="listDidblock",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",

     *     ),
     * )
     **/
    $api->get('didblock', 'App\Http\Controllers\Didcontroller@listDidblock');

    /**
     * @SWG\Put(
     *     path="/telephony/api/didblock{id}",
     *     tags={"Did Block"},
     *     summary="Update DID Block by ID for authorized user",
     *     description="",
     *     operationId="updateDidblock",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",

     *     ),
     * )
     **/
    $api->put('didblock/{id}', 'App\Http\Controllers\Didcontroller@updateDidblock');

    /**
     * @SWG\Delete(
     *     path="/telephony/api/didblock{id}",
     *     tags={"Did Block"},
     *     summary="Delete DID Block by ID for authorized user",
     *     description="This deletes the block and its child Dids",
     *     operationId="updateDidblock",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",

     *     ),
     * )
     **/
    $api->delete('didblock/{id}', 'App\Http\Controllers\Didcontroller@deleteDidblock');

    // List DIDs by block id
    /**
     * @SWG\Get(
     *     path="/telephony/api/didblock{id}/dids",
     *     tags={"Did"},
     *     summary="List DIDs for Did Block by ID for authorized user",
     *     description="List child DIDs for Did Block by ID",
     *     operationId="listDidbyBlockID",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",

     *     ),
     * )
     **/
    $api->get('didblock/{id}/dids', 'App\Http\Controllers\Didcontroller@listDidbyBlockID');


    // DID App routes
    // $api->post('did', 'App\Http\Controllers\Didcontroller@createDid'); // Individual DID creation not allowed.
        // List DIDs by block id

    /**
     * @SWG\Get(
     *     path="/telephony/api/did{id}",
     *     tags={"Did"},
     *     summary="Get DID by ID for authorized user",
     *     description="",
     *     operationId="getDid",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",

     *     ),
     * )
     **/
    $api->get('did/{id}', 'App\Http\Controllers\Didcontroller@getDid');

    /**
     * @SWG\Get(
     *     path="/telephony/api/did",
     *     tags={"Did"},
     *     summary="List DIDs for authorized user",
     *     description="This can be huge and need to add pagination",
     *     operationId="listDid",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",

     *     ),
     * )
     **/
    $api->get('did', 'App\Http\Controllers\Didcontroller@listDid');

    /**
     * @SWG\Put(
     *     path="/telephony/api/did{id}",
     *     tags={"Did"},
     *     summary="Update DID by ID for authorized user",
     *     description="This can be huge and need to add pagination",
     *     operationId="listDid",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     ),
     * )
     **/
    $api->put('did/{id}', 'App\Http\Controllers\Didcontroller@updateDid');
    // $api->delete('did/{id}', 'App\Http\Controllers\Didcontroller@deleteDid'); // Individual DID deletion Not allowed.
});

Route::auth();
