<?php


    /********************************
        Site Plan App routes
    ********************************/
    /**
     * @SWG\Post(
     *     path="/telephony/api/site_migration",
     *     tags={"Site Migration - Site"},
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
     *         name="comment",
     *         in="formData",
     *         description="comment",
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
     *
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
    $api->post('site_migration', 'App\Http\Controllers\SiteMigrationController@create_site_migration');

    /**
     * @SWG\Get(
     *     path="/telephony/api/site_migration",
     *     tags={"Site Migration - Site"},
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
    $api->get('site_migration', 'App\Http\Controllers\SiteMigrationController@list_site_migrations');

    /**
     * @SWG\Get(
     *     path="/telephony/api/site_migration/{id}",
     *     tags={"Site Migration - Site"},
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
    $api->get('site_migration/{id}', 'App\Http\Controllers\SiteMigrationController@get_site_migration');

    /**
     * @SWG\Get(
     *     path="/telephony/api/site_migrations/{sitecode}",
     *     tags={"Site Migration - Site"},
     *     summary="Get Site Plan by ID",
     *     description="",
     *     operationId="getsite",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="sitecode",
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
    $api->get('site_migrations/{sitecode}', 'App\Http\Controllers\SiteMigrationController@get_site_migration_by_sitecode');

    /**
     * @SWG\Put(
     *     path="/telephony/api/site_migration/{id}",
     *     tags={"Site Migration - Site"},
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
     *         name="comment",
     *         in="formData",
     *         description="comment",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="trunking",
     *         in="formData",
     *         description="Trunking",
     *		   enum={"sip", "local"},
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="e911",
     *         in="formData",
     *         description="E911 Type",
     *		   enum={"911enable", "local"},
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
    $api->put('site_migration/{id}', 'App\Http\Controllers\SiteMigrationController@update_site_migration');

    /**
     * @SWG\Delete(
     *     path="/telephony/api/site_migration/{id}",
     *     tags={"Site Migration - Site"},
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
    $api->delete('site_migration/{id}', 'App\Http\Controllers\SiteMigrationController@delete_site_migration');
