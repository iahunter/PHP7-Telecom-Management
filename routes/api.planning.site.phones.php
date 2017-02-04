<?php


    /********************************
        Site Phone Plan App routes
    ********************************/

    /**
     * @SWG\Post(
     *     path="/telephony/api/phone",
     *     tags={"Site Planning - Phone"},
     *     summary="Create New Phone in Site Plan",
     *     description="",
     *     operationId="createPhone",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="phoneplan",
     *         in="formData",
     *         description="Parent Phone Plan ID Number",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="site",
     *         in="formData",
     *         description="Parent Site ID Number",
     *         required=true,
     *         type="integer"
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
     *         required=true,
     *         type="boolean"
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
    $api->post('phone', 'App\Http\Controllers\SitePlanController@createPhone');

    // List phones by block id
    /**
     * @SWG\Get(
     *     path="/telephony/api/phone/site/{id}",
     *     tags={"Site Planning - Phone"},
     *     summary="List phones for Site Plan by ID for authorized user",
     *     description="List child phones for Site Plan by ID",
     *     operationId="listphonebySiteID",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Number of Site",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     ),
     * )
     **/
    $api->get('/phone/site/{id}', 'App\Http\Controllers\SitePlanController@listphonebySiteID');

    // phone App routes
    // $api->post('phone', 'App\Http\Controllers\SitePlanController@createphone'); // Individual phone creation not allowed.
        // List phones by block id

    /**
     * @SWG\Get(
     *     path="/telephony/api/phone/id/{id}",
     *     tags={"Site Planning - Phone"},
     *     summary="Get phone by ID for authorized user",
     *     description="",
     *     operationId="getphone",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of phone id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     ),
     * )
     **/
    $api->get('phone/id/{id}', 'App\Http\Controllers\SitePlanController@getphone');

    /**
     * @SWG\Get(
     *     path="/telephony/api/phone/number/{dn}",
     *     tags={"Site Planning - Phone"},
     *     summary="Get phone by number search for authorized user",
     *     description="",
     *     operationId="getphone",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="dn",
     *         in="path",
     *         description="Search Site Plans for Phones with DN Number",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     ),
     * )
     **/
    $api->get('phone/number/{dn}', 'App\Http\Controllers\SitePlanController@searchphoneNumber');

    /**
     * @SWG\Put(
     *     path="/telephony/api/phone/{id}",
     *     tags={"Site Planning - Phone"},
     *     summary="Update phone by ID for authorized user",
     *     description="This can be huge and need to add pagination",
     *     operationId="listphone",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of phone",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="phoneplan",
     *         in="formData",
     *         description="Parent Phone Plan ID Number",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="site",
     *         in="formData",
     *         description="Parent Site ID Number",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="device",
     *         in="formData",
     *         description="Device Type - Example: 7945, 8841, IP Communicator",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         in="formData",
     *         description="Phone Name - Example 0004DEADBEEF",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="firstname",
     *         in="formData",
     *         description="First Name - John",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="lastname",
     *         in="formData",
     *         description="Last Name - Doe",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="username",
     *         in="formData",
     *         description="User Name - John.Doe, CallManager.Unassign",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="dn",
     *         in="formData",
     *         description="Directory Number - Example: 4025551234",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="extlength",
     *         in="formData",
     *         description="Internal Extension Length - 4 digit is standard - Used for Internal Short Dialing",
     *		   enum={"4", "5", "10"},
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="language",
     *         in="formData",
     *         description="Language",
     *		   enum={"English", "French"},
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="voicemail",
     *         in="formData",
     *         description="Does user require a Voicemail Box with this DN?",
     *         required=false,
     *         type="boolean"
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
     * )
     **/
    $api->put('phone/{id}', 'App\Http\Controllers\SitePlanController@updatephone');
    // $api->delete('phone/{id}', 'App\Http\Controllers\SitePlanController@deletephone'); // Individual phone deletion Not allowed.

    /**
     * @SWG\Delete(
     *     path="/telephony/api/phone/{id}",
     *     tags={"Site Planning - Phone"},
     *     summary="Delete Phone from Site Plan by ID for authorized user",
     *     description="This deletes the phone by ID number",
     *     operationId="deletephone",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of Phone to Delete",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",

     *     ),
     * )
     **/
    $api->delete('phone/{id}', 'App\Http\Controllers\SitePlanController@deletephone');
