<?php

    /**
     * @SWG\Get(
     *     path="/telephony/api/egw/cisco_phone/all",
     *     tags={"Management - West 911Enable EGW"},
     *     summary="Seach for current user by alias",
     *     description="",
     *     operationId="getphones",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     ),
     *     @SWG\Response(
     *         response="401",
     *         description="Unauthorized user",
     *     ),
     * )
     **/
    $api->get('/egw/cisco_phone/all', 'App\Http\Controllers\West911EnableEGWController@get_all_cisco_phones');

    /**
     * @SWG\Get(
     *     path="/telephony/api/egw/cisco_phone/search/name/{name}",
     *     tags={"Management - West 911Enable EGW"},
     *     summary="Seach for current user by alias",
     *     description="",
     *     operationId="getphones",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="name",
     *         in="path",
     *         description="Name of Phone",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     ),
     *     @SWG\Response(
     *         response="401",
     *         description="Unauthorized user",
     *     ),
     * )
     **/
    $api->get('/egw/cisco_phone/search/name/{name}', 'App\Http\Controllers\West911EnableEGWController@get_cisco_phone_by_name');

    /**
     * @SWG\Get(
     *     path="/telephony/api/egw/endpoint/all",
     *     tags={"Management - West 911Enable EGW"},
     *     summary="Seach for current user by alias",
     *     description="",
     *     operationId="getphones",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     ),
     *     @SWG\Response(
     *         response="401",
     *         description="Unauthorized user",
     *     ),
     * )
     **/
    $api->get('/egw/endpoint/all', 'App\Http\Controllers\West911EnableEGWController@get_all_endpoints');

    /**
     * @SWG\Get(
     *     path="/telephony/api/egw/endpoint/search/name/{name}",
     *     tags={"Management - West 911Enable EGW"},
     *     summary="Seach for current user by alias",
     *     description="",
     *     operationId="getphones",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="name",
     *         in="path",
     *         description="Name of Phone",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     ),
     *     @SWG\Response(
     *         response="401",
     *         description="Unauthorized user",
     *     ),
     * )
     **/
    $api->get('/egw/endpoint/search/name/{name}', 'App\Http\Controllers\West911EnableEGWController@get_endpoint_by_name');

    /**
     * @SWG\Get(
     *     path="/telephony/api/egw/endpoint_erlid/all",
     *     tags={"Management - West 911Enable EGW"},
     *     summary="Seach for current user by alias",
     *     description="",
     *     operationId="getphones",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     ),
     *     @SWG\Response(
     *         response="401",
     *         description="Unauthorized user",
     *     ),
     * )
     **/
    $api->get('/egw/endpoint_erlid/all', 'App\Http\Controllers\West911EnableEGWController@get_all_endpoints_ip_erl');

    /**
     * @SWG\Get(
     *     path="/telephony/api/egw/erls/all",
     *     tags={"Management - West 911Enable EGW"},
     *     summary="Seach for current user by alias",
     *     description="",
     *     operationId="getphones",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     ),
     *     @SWG\Response(
     *         response="401",
     *         description="Unauthorized user",
     *     ),
     * )
     **/
    $api->get('/egw/erls/all', 'App\Http\Controllers\West911EnableEGWController@list_erls');
	
	/**
     * @SWG\Get(
     *     path="/telephony/api/egw/list_erls_and_phone_count_by_erl",
     *     tags={"Management - West 911Enable EGW"},
     *     summary="List ERLs with Count of Phones by ERL",
     *     description="",
     *     operationId="listPhoneModels",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *
     *     ),
     * )
     **/
    $api->get('/egw/list_erls_and_phone_count_by_erl', 'App\Http\Controllers\West911EnableEGWController@list_erls_and_phone_counts');
