<?php

    /**
     * @SWG\Get(
     *     path="/api/reports",
     *     tags={"Reports"},
     *     summary="Get List of Reports",
     *     description="",
     *     operationId="listReportTypes",
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
    $api->get('reports', 'App\Http\Controllers\ReportsController@listReportTypes');
	
	 /**
     * @SWG\Get(
     *     path="/api/reports/id/{id}",
     *     tags={"Reports"},
     *     summary="Get List of Reports Devices",
     *     description="",
     *     operationId="getSite",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="type",
     *         in="path",
     *         description="ID of Device",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of Device",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     * 	   ),
     *     @SWG\Response(
     *         response="401",
     *         description="Unauthorized user",
     *     ),
     * )
     **/
    $api->get('reports/id/{id}', 'App\Http\Controllers\TelecomInfrastructureController@getDevice');

    /**
     * @SWG\Post(
     *     path="/api/reports",
     *     tags={"Reports"},
     *     summary="Create Telecom Device",
     *     description="",
     *     operationId="createDevice",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="hostname",
     *         in="formData",
     *         description="Name of Device",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="comment",
     *         in="formData",
     *         description="Comment",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="role",
     *         in="formData",
     *         description="Role Information",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="manufacture",
     *         in="formData",
     *         description="manufacture",
     *         required=false,
     *         type="string"
     *     ),
     *	   @SWG\Parameter(
     *         name="model",
     *         in="formData",
     *         description="model",
     *         required=false,
     *         type="string"
     *     ),
     *	   @SWG\Parameter(
     *         name="software_version",
     *         in="formData",
     *         description="software_version",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="ip_address",
     *         in="formData",
     *         description="ip_address",
     *         required=false,
     *         type="string"
     *     ),
     *	   @SWG\Parameter(
     *         name="mgmt_url",
     *         in="formData",
     *         description="Management URL",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="location",
     *         in="formData",
     *         description="Sitecode",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="monitor",
     *         in="formData",
     *         description="Active Monitoring",
     *         required=false,
     *         type="boolean"
     *     ),
     *     @SWG\Parameter(
     *         name="json",
     *         in="formData",
     *         description="json",
     *         required=false,
     *         type="array",
     *         @SWG\Items(
     *             type="string",
     *             description="H323 Gateways",
     *         ),
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->post('reports', 'App\Http\Controllers\TelecomInfrastructureController@createDevice');

    /**
     * @SWG\Put(
     *     path="/api/reports/id/{id}",
     *     tags={"Reports"},
     *     summary="Update Telecom Device by ID for authorized user",
     *     description="updateDevice",
     *     operationId="updateDidblock",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of block id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="hostname",
     *         in="formData",
     *         description="Name of Device",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="comment",
     *         in="formData",
     *         description="Comment",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="role",
     *         in="formData",
     *         description="Role Information",
     *         required=false,
     *         type="string"
     *     ),
     *
     *     @SWG\Parameter(
     *         name="manufacture",
     *         in="formData",
     *         description="manufacture",
     *         required=false,
     *         type="string"
     *     ),
     *	   @SWG\Parameter(
     *         name="model",
     *         in="formData",
     *         description="model",
     *         required=false,
     *         type="string"
     *     ),
     *	   @SWG\Parameter(
     *         name="software_version",
     *         in="formData",
     *         description="software_version",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="ip_address",
     *         in="formData",
     *         description="ip_address",
     *         required=false,
     *         type="string"
     *     ),
     *	   @SWG\Parameter(
     *         name="mgmt_url",
     *         in="formData",
     *         description="Management URL",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="location",
     *         in="formData",
     *         description="Sitecode",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="monitor",
     *         in="formData",
     *         description="Active Monitoring",
     *         required=false,
     *         type="boolean"
     *     ),
     *     @SWG\Parameter(
     *         name="json",
     *         in="formData",
     *         description="json",
     *         required=false,
     *         type="array",
     *         @SWG\Items(
     *             type="string",
     *             description="H323 Gateways",
     *         ),
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *
     *     ),
     * )
     **/
    $api->put('reports/id/{id}', 'App\Http\Controllers\TelecomInfrastructureController@updateDevice');

    /**
     * @SWG\Delete(
     *     path="/api/reports/id/{id}",
     *     tags={"Reports"},
     *     summary="Delete Device by ID for authorized user",
     *     description="This deletes the block and its child Dids",
     *     operationId="deleteDevice",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of block to Delete",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *
     *     ),
     * )
     **/
    $api->delete('reports/id/{id}', 'App\Http\Controllers\TelecomInfrastructureController@deleteDevice');

