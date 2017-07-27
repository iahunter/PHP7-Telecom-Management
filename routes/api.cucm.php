<?php

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/ldap/start",
     *     tags={"Management - CUCM"},
     *     summary="Start LDAP Sync",
     *     description="",
     *     operationId="",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->get('cucm/ldap/start', 'App\Http\Controllers\Cucm@start_ldap_sync');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/ldap/stop",
     *     tags={"Management - CUCM"},
     *     summary="Stop LDAP Sync",
     *     description="",
     *     operationId="",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->get('cucm/ldap/stop', 'App\Http\Controllers\Cucm@stop_ldap_sync');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/ldap/status",
     *     tags={"Management - CUCM"},
     *     summary="Get LDAP Sync Status",
     *     description="",
     *     operationId="",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->get('cucm/ldap/status', 'App\Http\Controllers\Cucm@get_ldap_sync_status');

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
     *     path="/telephony/api/cucm/dateandtime",
     *     tags={"Management - CUCM"},
     *     summary="List Date Time Groups",
     *     description="",
     *     operationId="listDateTimeGroup",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->get('cucm/dateandtime', 'App\Http\Controllers\Cucm@listDateTimeGroup');

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
     *     ),
     *     @SWG\Response(
     *         response="401",
     *         description="Unauthorized user",
     *     ),
     * )
     **/
    $api->get('cucm/css/{name}', 'App\Http\Controllers\Cucm@listCssDetailsbyName');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/routepattern/{routePartitionName}",
     *     tags={"Management - CUCM"},
     *     summary="List Route Patterns details by Partition",
     *     description="",
     *     operationId="listRoutePatternbyPartition",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     * 	   @SWG\Parameter(
     *         name="routePartitionName",
     *         in="path",
     *         description="Route Partition",
     *         required=true,
     *         type="string"
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
    $api->get('cucm/routepattern/{routePartitionName}', 'App\Http\Controllers\Cucm@listRoutePatternsByPartition');
	
	/**
     * @SWG\Get(
     *     path="/telephony/api/cucm/routeplan/summary/{number}",
     *     tags={"Management - CUCM"},
     *     summary="List Route Plan Numbers Summary by Number",
     *     description="",
     *     operationId="listRoutePatternbyPartition",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     * 	   @SWG\Parameter(
     *         name="number",
     *         in="path",
     *         description="Search Route Plan for number in CUCM",
     *         required=true,
     *         type="string"
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
    $api->get('cucm/routeplan/summary/{number}', 'App\Http\Controllers\Cucm@getNumberbyRoutePlan');
	
	/**
     * @SWG\Get(
     *     path="/telephony/api/cucm/routeplan/details/{number}",
     *     tags={"Management - CUCM"},
     *     summary="List Route Plan Numbers Details by Number",
     *     description="",
     *     operationId="listRoutePatternbyPartition",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     * 	   @SWG\Parameter(
     *         name="number",
     *         in="path",
     *         description="Search Route Plan for number in CUCM",
     *         required=true,
     *         type="string"
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
    $api->get('cucm/routeplan/details/{number}', 'App\Http\Controllers\Cucm@getNumberandDeviceDetailsbyRoutePlan');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/search/{type}/{name}",
     *     tags={"Management - CUCM"},
     *     summary="List Object Type details by Name",
     *     description="",
     *     operationId="getObjectTypebyName",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="name",
     *         in="path",
     *         description="Name of Object",
     *         required=true,
     *         type="string"
     *     ),
     *	   @SWG\Parameter(
     *         name="type",
     *         in="path",
     *         description="Object Type",
     *         required=true,
     *         type="string"
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
    $api->get('cucm/search/{type}/{name}', 'App\Http\Controllers\Cucm@getObjectTypebyName');
	
	    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/searchsite/{type}/{sitecode}",
     *     tags={"Management - CUCM"},
     *     summary="List Object Type details by Site",
     *     description="",
     *     operationId="getObjectTypebyName",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="sitecode",
     *         in="path",
     *         description="Name of Object",
     *         required=true,
     *         type="string"
     *     ),
     *	   @SWG\Parameter(
     *         name="type",
     *         in="path",
     *         description="Object Type",
     *         required=true,
     *         type="string"
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
    $api->get('cucm/searchsite/{type}/{sitecode}', 'App\Http\Controllers\Cucm@getObjectTypebySite');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/searchuuid/{type}/{uuid}",
     *     tags={"Management - CUCM"},
     *     summary="List Object Type details by UUID",
     *     description="",
     *     operationId="getObjectTypebyName",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="Name of Object",
     *         required=true,
     *         type="string"
     *     ),
     *	   @SWG\Parameter(
     *         name="type",
     *         in="path",
     *         description="Object Type",
     *         required=true,
     *         type="string"
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
    $api->get('cucm/searchuuid/{type}/{uuid}', 'App\Http\Controllers\Cucm@getObjectTypebyUUID');
