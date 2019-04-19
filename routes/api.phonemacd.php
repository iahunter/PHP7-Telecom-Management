<?php

    /**
     * @SWG\Post(
     *     path="/telephony/api/cucm/macd/batch",
     *     tags={"Management - Cisco Voice - MACD"},
     *     summary="Create New MACD Batch - Updates AD, CUCM, and Unity Connection",
     *     description="This sends the variables to the queue where they get worked. The results are stored in the PhoneMACD Table.",
     *     operationId="createSite",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="macds",
     *         in="formData",
     *         description="Phone MACD Array",
     *         required=true,
     *         type="array",
     *         @SWG\Items(
     *             type="array",
	 *				 	@SWG\Items(
	 *					type="string",
     *             		description="MACDs",
	 *				),
     *         ),
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
    $api->post('cucm/macd/batch', 'App\Http\Controllers\PhoneMACDController@createPhoneMacdBatch');

    /**
     * @SWG\Post(
     *     path="/telephony/api/cucm/macd/add",
     *     tags={"Management - Cisco Voice - MACD"},
     *     summary="Create New MACD - Updates AD, CUCM, and Unity Connection",
     *     description="This sends the variables to the queue where they get worked. The results are stored in the PhoneMACD Table.",
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
     *         description="Device Type - Example: 7945, 8841, IP Communicator",
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
     *         description="User Name - John.Doe, CallManager.Unassign",
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
     *         name="usenumber",
     *         in="formData",
     *         description="Create a new directory number or use existing number",
     *		   enum={"new", "existing"},
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
     *         name="template",
     *         in="formData",
     *         description="Voicemail Template - Required of voicemail is true",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="phoneplan_id",
     *         in="formData",
     *         description="Phone Plan ID this MAC is associated to. For use inside Phone plans only",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="ticket_number",
     *         in="formData",
     *         description="Ticket Number",
     *         required=false,
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
    $api->post('cucm/macd/add', 'App\Http\Controllers\PhoneMACDController@createPhoneMACD_Phone');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/macd/list/week/user",
     *     tags={"Management - Cisco Voice - MACD"},
     *     summary="List of Phone MACDs created by authorized user",
     *     description="",
     *     operationId="listMyMacds",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *
     *     ),
     * )
     **/
    $api->get('cucm/macd/list/week/user', 'App\Http\Controllers\PhoneMACDController@list_my_macd_jobs_for_week');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/macd/parentlist/week/user",
     *     tags={"Management - Cisco Voice - MACD"},
     *     summary="List of Phone MACDs Parents created by authorized user",
     *     description="",
     *     operationId="listMyMacds",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *
     *     ),
     * )
     **/
    $api->get('cucm/macd/parentlist/week/user', 'App\Http\Controllers\PhoneMACDController@list_my_macd_parents_for_week');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/macd/list/week",
     *     tags={"Management - Cisco Voice - MACD"},
     *     summary="List of Phone MACDs for last week.",
     *     description="",
     *     operationId="listMyMacds",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *
     *     ),
     * )
     **/
    $api->get('cucm/macd/list/week', 'App\Http\Controllers\PhoneMACDController@list_macd_jobs_for_week');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/macd/parentlist/week",
     *     tags={"Management - Cisco Voice - MACD"},
     *     summary="List of Phone MACDs Parents for last week.",
     *     description="",
     *     operationId="listMyMacds",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *
     *     ),
     * )
     **/
    $api->get('cucm/macd/parentlist/week', 'App\Http\Controllers\PhoneMACDController@list_macd_parents_for_week');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/macd/parentlist",
     *     tags={"Management - Cisco Voice - MACD"},
     *     summary="List of Phone MACDs Parents - Limit 1000",
     *     description="",
     *     operationId="listMyMacds",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *
     *     ),
     * )
     **/
    $api->get('cucm/macd/parentlist', 'App\Http\Controllers\PhoneMACDController@list_macd_parents');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/macd/phoneplan/{id}",
     *     tags={"Management - Cisco Voice - MACD"},
     *     summary="List of Phone MACD Parents by Phone Plan ID",
     *     description="",
     *     operationId="listMACDbyPhonePlanId",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of Parent",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->get('/cucm/macd/phoneplan/{id}', 'App\Http\Controllers\PhoneMACDController@list_macd_parents_by_phoneplan_id');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/macd/list/tasks/{id}",
     *     tags={"Management - Cisco Voice - MACD"},
     *     summary="List of Phone MACD Childrent tasks",
     *     description="",
     *     operationId="listMyMacdsChildren",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of Parent",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->get('/cucm/macd/list/tasks/{id}', 'App\Http\Controllers\PhoneMACDController@list_macd_and_children_by_id');

    /**
     * @SWG\Delete(
     *     path="/telephony/api/cucm/macd/{id}",
     *     tags={"Management - Cisco Voice - MACD"},
     *     summary="Delete MACD Log ID",
     *     description="This deletes the logid and its children jobs",
     *     operationId="deleteMACD",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of MACD to Delete",
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
    $api->delete('/cucm/macd/{id}', 'App\Http\Controllers\PhoneMACDController@deletePhoneMACD');
