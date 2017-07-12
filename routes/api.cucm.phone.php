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
     *         description="Name of Phone - Example SEP0004DEADBEEF",
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
     *         description="Name of Phone to Delete - Example SEP0004DEADBEEF",
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

    /**
     * @SWG\Put(
     *     path="/telephony/api/cucm/phone",
     *     tags={"Management - CUCM - Phone Provisioning"},
     *     summary="Update Phone in CUCM",
     *     description="Update Phone",
     *     operationId="insert phones",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="phone",
     *         in="formData",
     *         description="This requires the correct values to be passed.",
     *         required=true,
     *         type="array",
     *         @SWG\Items(
     *             type="string",
     *             description="Phone",
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
    $api->put('cucm/phone', 'App\Http\Controllers\Cucmphone@updatePhone');

    /**
     * @SWG\Post(
     *     path="/telephony/api/cucm/upload/phones",
     *     tags={"Management - CUCM - Phone Provisioning"},
     *     summary="Upload Phone Planning Document in CUCM",
     *     description="Upload .csv file of phones",
     *     operationId="createSite",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="phones",
     *         in="formData",
     *         description="Sitecode",
     *         required=true,
     *         type="file",
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
    $api->post('cucm/upload/phones', 'App\Http\Controllers\Cucmphone@uploadPhones');

    /**
     * @SWG\Post(
     *     path="/telephony/api/cucm/paste/phones",
     *     tags={"Management - CUCM - Phone Provisioning"},
     *     summary="Upload Phone Planning Document in CUCM",
     *     description="
     Paste phones in from excel into the text box area. This must be in the correct format to be parsed correctly.
     See phone planning template for correct format. Do not include headers when pasting.

     phones format:
     'First Name'	'Last Name'	'Username'	'MAC Address'	'Device Type'	'10 digit Extension'	'Language'	'Default Password for User ID Access'	U'nity Mailbox Y/N'	'NOTES'
     ",
     *     operationId="insert phones",
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
     *         name="extlength",
     *         in="formData",
     *         description="Internal Extension Length - 4 digit is standard - Used for Internal Short Dialing",
     *		   enum={"4", "5", "10"},
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="phones",
     *         in="formData",
     *         description="Paste in data from phone planning template - Do not include header info. Follow Format above in notes.",
     *         required=true,
     *         type="array",
     *         @SWG\Items(
     *             type="string",
     *             description="Phones",
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
    $api->post('cucm/paste/phones', 'App\Http\Controllers\Cucmphone@pastePhones');
