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
