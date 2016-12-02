<?php

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/phone/{name}",
     *     tags={"Management - CUCM - Phone Provisioning"},
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
     *     ),
     *     @SWG\Response(
     *         response="401",
     *         description="Unauthorized user",
     *     ),
     * )
     **/
    $api->get('cucm/phone/{name}', 'App\Http\Controllers\Cucmphone@getPhone');
	
	
	 /**
     * @SWG\Delete(
     *     path="/telephony/api/cucm/phone/{name}",
     *     tags={"Management - CUCM - Phone Provisioning"},
     *     summary="Remove Phone by Name",
     *     description="",
     *     operationId="deletePhone",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="name",
     *         in="path",
     *         description="Name of Phone to Delete",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",

     *     ),
     * )
     **/
    $api->delete('cucm/phone/{name}', 'App\Http\Controllers\Cucmphone@deletePhone');
	
	
	/**
     * @SWG\Post(
     *     path="/telephony/api/cucm/phone",
     *     tags={"Management - CUCM - Phone Provisioning"},
     *     summary="Create New Phone in CUCM",
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
     *         name="device",
     *         in="formData",
     *         description="Device Type",
     *         required=true,
     *         type="string"
     *     ),
	 *     @SWG\Parameter(
     *         name="name",
     *         in="formData",
     *         description="Phone Name - Example 0004DEADBEEF",
     *         required=true,
     *         type="string"
     *     ),
	 *     @SWG\Parameter(
     *         name="firstname",
     *         in="formData",
     *         description="First Name - John",
     *         required=true,
     *         type="string"
     *     ),
	 *     @SWG\Parameter(
     *         name="lastname",
     *         in="formData",
     *         description="Last Name - Doe",
     *         required=true,
     *         type="string"
     *     ),
	 *     @SWG\Parameter(
     *         name="username",
     *         in="formData",
     *         description="User Name - John.Doe",
     *         required=true,
     *         type="string"
     *     ),
	 *     @SWG\Parameter(
     *         name="dn",
     *         in="formData",
     *         description="Directory Number - Example: 4025551234",
     *         required=true,
     *         type="string"
     *     ),
	 *     @SWG\Parameter(
     *         name="extlength",
     *         in="formData",
     *         description="Internal Extension Length - 4 digit is standard - Used for Internal Short Dialing",
	 *		   enum={"4", "5", "10"},
     *         required=true,
     *         type="string"
     *     ),
	 *     @SWG\Parameter(
     *         name="language",
     *         in="formData",
     *         description="Language",
	 *		   enum={"English", "French"},
     *         required=true,
     *         type="string"
     *     ),
	 *     @SWG\Parameter(
     *         name="voicemail",
     *         in="formData",
     *         description="Does user require a Voicemail Box with this DN?",
	 *		   enum={"true", "false"},
     *         required=true,
     *         type="string"
     *     ),
	 *     @SWG\Parameter(
     *         name="notes",
     *         in="formData",
     *         description="Notes",
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
	$api->post('cucm/phone', 'App\Http\Controllers\Cucmphone@createPhone');
