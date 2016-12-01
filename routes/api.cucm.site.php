<?php

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/sites",
     *     tags={"Management - CUCM - Site Provisioning"},
     *     summary="Get List of Sites from CUCM Device Pools",
     *     description="",
     *     operationId="getDidblock",
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
    $api->get('cucm/sites', 'App\Http\Controllers\Cucmsite@listsites');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/site/summary/{name}",
     *     tags={"Management - CUCM - Site Provisioning"},
     *     summary="Get Site Summary by Name",
     *     description="",
     *     operationId="getSite",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="name",
     *         in="path",
     *         description="Name of Site",
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
    $api->get('cucm/site/summary/{name}', 'App\Http\Controllers\Cucmsite@getSite');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/site/details/{name}",
     *     tags={"Management - CUCM - Site Provisioning"},
     *     summary="Get Site Details by Name",
     *     description="",
     *     operationId="getSite",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="name",
     *         in="path",
     *         description="ID of block id",
     *         required=true,
     *         type="integer"
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
    $api->get('cucm/site/details/{name}', 'App\Http\Controllers\Cucmsite@getSiteDetails');


    /**
     * @SWG\Post(
     *     path="/telephony/api/cucm/site",
     *     tags={"Management - CUCM - Site Provisioning"},
     *     summary="Create New Site in CUCM",
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
     *         description="NAP (###) - Area Code",
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
    $api->post('cucm/site', 'App\Http\Controllers\Cucmsite@createSite');
