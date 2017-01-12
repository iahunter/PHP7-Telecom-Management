<?php


    /********************************
        Site Plan App routes
    ********************************/
    /**
     * @SWG\Post(
     *     path="/telephony/api/site",
     *     tags={"Site Planning - Site"},
     *     summary="Create Site Plan",
     *     description="
     Select the correct Site Design Type according to your site's specific requirements.
     Type 1 - Centralized SIP Trunking and Centralized E911
     Type 2 - Local Gateway Trunking but using Centralized E911
     Type 3 - Centralized SIP Trunking but leveraging local gateway/SRST for 911
     Type 4 - Local Gateway Trunking and 911",
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
     *         description="Design Type - See Implementation Notes Above",
     *		   enum={"1", "2", "3", "4"},
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="srstip",
     *         in="formData",
     *         description="SRST IP Address - Not required for Type 1 Designs. Recommended but not required for Type 2,3, and 4",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="h323ip",
     *         in="formData",
     *         description="These are required for Design Type 2,3,and 4. If multiple H323 Gateways, enter each one on new line. These will get added to the site route group. ",
     *         required=false,
     *         type="array",
     *         @SWG\Items(
     *             type="string",
     *             description="H323 Gateways",
     *         ),
     *     ),
     *	   @SWG\Parameter(
     *         name="timezone",
     *         in="formData",
     *         description="TimeZone and Format - These are prebuilt in CUCM and may need customized to your environment. ",
     *		   enum={"Alaska-12", "Arizona-12", "Central-12", "Eastern-12", "Hawaii-12", "Mountain-12", "Pacific-12"},
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="npa",
     *         in="formData",
     *         description="NPA (###) - Area Code",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="nxx",
     *         in="formData",
     *         description="NXX (###) - Prefix",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="didrange",
     *         in="formData",
     *         description="Last 4 digit Ranges. Use Regex to represent the DID Ranges. Use multiple Lines to represent multiple ranges. Example: 40[2-9]X",
     *         required=true,
     *         type="array",
     *         @SWG\Items(
     *             type="string",
     *             description="",
     *         ),
     *     ),
     *     @SWG\Parameter(
     *         name="operator",
     *         in="formData",
     *         description="Operator Last 4 digits of DID",
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
    $api->post('site', 'App\Http\Controllers\SitePlanController@createsite');

    /**
     * @SWG\Get(
     *     path="/telephony/api/site/{id}",
     *     tags={"Site Planning - Site"},
     *     summary="Get Site Plan by ID",
     *     description="",
     *     operationId="getsite",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
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
    $api->get('site/{id}', 'App\Http\Controllers\SitePlanController@getsite');

    /**
     * @SWG\Get(
     *     path="/telephony/api/site",
     *     tags={"Site Planning - Site"},
     *     summary="List of Site Plans for authorized user",
     *     description="",
     *     operationId="listsite",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",

     *     ),
     * )
     **/
    $api->get('site', 'App\Http\Controllers\SitePlanController@listSites');

    /**
     * @SWG\Put(
     *     path="/telephony/api/site/{id}",
     *     tags={"Site Planning - Site"},
     *     summary="Update Site Plan by ID for authorized user",
     *     description="
     Select the correct Site Design Type according to your site's specific requirements.
     Type 1 - Centralized SIP Trunking and Centralized E911
     Type 2 - Local Gateway Trunking but using Centralized E911
     Type 3 - Centralized SIP Trunking but leveraging local gateway/SRST for 911
     Type 4 - Local Gateway Trunking and 911",
     *     operationId="createSite",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Number of Site",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="sitecode",
     *         in="formData",
     *         description="Sitecode",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="type",
     *         in="formData",
     *         description="Design Type - See Implementation Notes Above",
     *		   enum={"1", "2", "3", "4"},
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="srstip",
     *         in="formData",
     *         description="SRST IP Address - Not required for Type 1 Designs. Recommended but not required for Type 2,3, and 4",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="h323ip",
     *         in="formData",
     *         description="These are required for Design Type 2,3,and 4. If multiple H323 Gateways, enter each one on new line. These will get added to the site route group. ",
     *         required=false,
     *         type="array",
     *         @SWG\Items(
     *             type="string",
     *             description="H323 Gateways",
     *         ),
     *     ),
     *	   @SWG\Parameter(
     *         name="timezone",
     *         in="formData",
     *         description="TimeZone and Format - These are prebuilt in CUCM and may need customized to your environment. ",
     *		   enum={"Alaska-12", "Arizona-12", "Central-12", "Eastern-12", "Hawaii-12", "Mountain-12", "Pacific-12"},
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="npa",
     *         in="formData",
     *         description="NPA (###) - Area Code",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="nxx",
     *         in="formData",
     *         description="NXX (###) - Prefix",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="didrange",
     *         in="formData",
     *         description="Last 4 digit Ranges. Use Regex to represent the DID Ranges. Use multiple Lines to represent multiple ranges. Example: 40[2-9]X",
     *         required=false,
     *         type="array",
     *         @SWG\Items(
     *             type="string",
     *             description="",
     *         ),
     *     ),
     *     @SWG\Parameter(
     *         name="operator",
     *         in="formData",
     *         description="Operator Last 4 digits of DID",
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
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",

     *     ),
     * )
     **/
    $api->put('site/{id}', 'App\Http\Controllers\SitePlanController@updatesite');

    /**
     * @SWG\Delete(
     *     path="/telephony/api/site/{id}",
     *     tags={"Site Planning - Site"},
     *     summary="Delete Site Plan by ID for authorized user",
     *     description="This deletes the block and its child phones",
     *     operationId="deletesite",
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

     *     ),
     * )
     **/
    $api->delete('site/{id}', 'App\Http\Controllers\SitePlanController@deletesite');

    /**
     * @SWG\Post(
     *     path="/telephony/api/site/phone",
     *     tags={"Site Planning - Phone"},
     *     summary="Create New Phone in Site Plan",
     *     description="",
     *     operationId="createPhone",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="parent",
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
    $api->post('site/phone', 'App\Http\Controllers\SitePlanController@createPhone');
	
    // List phones by block id
    /**
     * @SWG\Get(
     *     path="/telephony/api/site/{id}/phones",
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
    $api->get('site/{id}/phones', 'App\Http\Controllers\SitePlanController@listphonebySiteID');

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
     * @SWG\Get(
     *     path="/telephony/api/phone/searchbyparent/{parentid}/{column}/{search}",
     *     tags={"Site Planning - Phone"},
     *     summary="Search phone by parent ID and column search for authorized user",
     *     description="",
     *     operationId="getphone",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="parentid",
     *         in="path",
     *         description="ID of parent",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="column",
     *         in="path",
     *         description="Column to Search",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="search",
     *         in="path",
     *         description="Search String",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",

     *     ),
     * )
     **/
    $api->get('phone/searchbyparent/{parentid}/{column}/{search}', 'App\Http\Controllers\SitePlanController@searchphonebyParent');

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
     *         name="parent",
     *         in="formData",
     *         description="Parent Site ID Number",
     *         required=false,
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
