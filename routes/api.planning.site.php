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
     * Select the correct Site Design Type according to your site's specific requirements.
     * Type 1 - Centralized SIP Trunking and Centralized E911
     * Type 2 - Local Gateway Trunking but using Centralized E911
     * Type 3 - Centralized SIP Trunking but leveraging local gateway/SRST for 911
     * Type 4 - Local Gateway Trunking and 911",
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
     *         name="trunking",
     *         in="formData",
     *         description="Trunking",
     *		   enum={"sip", "local"},
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="e911",
     *         in="formData",
     *         description="E911 Type",
     *		   enum={"911enable", "local"},
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
     *         name="didblocks",
     *         in="formData",
     *         description="DID Block IDs assigned to the site.",
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
     *	   @SWG\Parameter(
     *         name="extlen",
     *         in="formData",
     *         description="Extension Length for internal dialling. Sites with last 4 digits starting with 9 need to be 5 or 10",
     *		   enum={"4", "5", "10"},
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="languages",
     *         in="formData",
     *         description="Languages that are used at the site. One per line. (english, french)",
     *         required=false,
     *         type="array",
     *         @SWG\Items(
     *             type="string",
     *             description="",
     *         ),
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
     *     path="/telephony/api/site/{id}/phoneplans",
     *     tags={"Site Planning - Site"},
     *     summary="Get Site Plan Phone Plans by ID",
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
    $api->get('site/{id}/phoneplans', 'App\Http\Controllers\SitePlanController@listphoneplanbysiteid');

    /**
     * @SWG\Get(
     *     path="/telephony/api/site/{id}/phones",
     *     tags={"Site Planning - Site"},
     *     summary="Get Site Plan Phones by ID",
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
    $api->get('site/{id}/phones', 'App\Http\Controllers\SitePlanController@listphonesbysiteid');

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
     *
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
     * Select the correct Site Design Type according to your site's specific requirements.
     * Type 1 - Centralized SIP Trunking and Centralized E911
     * Type 2 - Local Gateway Trunking but using Centralized E911
     * Type 3 - Centralized SIP Trunking but leveraging local gateway/SRST for 911
     * Type 4 - Local Gateway Trunking and 911",
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
     *
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
     *
     *     ),
     * )
     **/
    $api->delete('site/{id}', 'App\Http\Controllers\SitePlanController@deletesite');
