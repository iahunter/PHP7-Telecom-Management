<?php

	
	/**
     * @SWG\Get(
     *     path="/telephony/api/cucm/user/{username}",
     *     tags={"Management - CUCM - User Provisioning"},
     *     summary="Get User by Username",
     *     description="",
     *     operationId="getUser",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="username",
     *         in="path",
     *         description="Username",
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
    $api->get('cucm/user/{username}', 'App\Http\Controllers\CucmUser@getUserbyUsername');
	
	
	/**
     * @SWG\Post(
     *     path="/telephony/api/cucm/user",
     *     tags={"Management - CUCM - User Provisioning"},
     *     summary="Create New Local End User in CUCM",
     *     description="",
     *     operationId="addUser",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="firstname",
     *         in="formData",
     *         description="firstname",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="lastname",
     *         in="formData",
     *         description="firstname",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="username",
     *         in="formData",
     *         description="firstname",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="digestCredentials",
     *         in="formData",
     *         description="SIP Digest Password",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="dn",
     *         in="formData",
     *         description="Telephone Number",
     *         required=false,
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
    $api->post('cucm/user', 'App\Http\Controllers\CucmUser@createUser');
