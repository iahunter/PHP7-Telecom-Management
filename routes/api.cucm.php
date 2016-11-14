<?php

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/phone/{name}",
     *     tags={"Management - CUCM"},
     *     summary="Get Phone Details by Name",
     *     description="",
     *     operationId="getPhone",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="name",
     *         in="path",
     *         description="Name of Phone",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->get('cucm/phone/{name}', 'App\Http\Controllers\Cucm@getPhone');


    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/css",
     *     tags={"Management - CUCM"},
     *     summary="List Css details",
     *     description="",
     *     operationId="getCssDetails",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->get('cucm/css', 'App\Http\Controllers\Cucm@listCssDetails');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/css/{name}",
     *     tags={"Management - CUCM"},
     *     summary="List Css details by Name",
     *     description="",
     *     operationId="getCssbyName",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="name",
     *         in="path",
     *         description="Name of Css",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->get('cucm/css/{name}', 'App\Http\Controllers\Cucm@listCssDetailsbyName');
	

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/sites",
     *     tags={"Management - CUCM - Site Provisioning"},
     *     summary="Get List of Sites from CUCM Device Pools",
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
    $api->get('cucm/sites', 'App\Http\Controllers\Cucmsite@listsites');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/site/{name}",
     *     tags={"Management - CUCM - Site Provisioning"},
     *     summary="Get Site Summary by Name",
     *     description="",
     *     operationId="getSite",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="name",
     *         in="path",
     *         description="ID of block id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->get('cucm/site/{name}', 'App\Http\Controllers\Cucmsite@getSite');
	
	/**
     * @SWG\Get(
     *     path="/telephony/api/cucm/site/details/{name}",
     *     tags={"Management - CUCM - Site Provisioning"},
     *     summary="Get Site Details by Name",
     *     description="",
     *     operationId="getSite",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="name",
     *         in="path",
     *         description="ID of block id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->get('cucm/site/details/{name}', 'App\Http\Controllers\Cucmsite@getSiteDetails');
	
	
	/**
     * @SWG\Post(
     *     path="/telephony/api/cucm/site",
     *     tags={"Management - CUCM - Site Provisioning"},
     *     summary="Create New Site in CUCM",
     *     description="",
     *     operationId="createSite",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="sitecode",
     *         in="formData",
     *         description="Sitecode",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="type",
     *         in="formData",
     *         description="Design Type",
     *		   enum={"1", "2", "3", "4"},
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="srstip",
     *         in="formData",
     *         description="SRST IP Address",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="h323ip",
     *         in="formData",
     *         description="H323 Gateway",
     *         required=false,
     *         type="string",
     *     ),
     *	   @SWG\Parameter(
     *         name="timezone",
     *         in="formData",
     *         description="TimeZone and Format",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="npa",
     *         in="formData",
     *         description="NAP (###)",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="nxx",
     *         in="formData",
     *         description="NXX (###)",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="didrange",
     *         in="formData",
     *         description="Example: 40[2-9]X",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="operator",
     *         in="formData",
     *         description="Operator Last 4 digits of DID",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->post('cucm/site', 'App\Http\Controllers\Cucmsite@createSite');
