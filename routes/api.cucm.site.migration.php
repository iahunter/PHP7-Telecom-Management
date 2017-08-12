<?php

    /**
     * @SWG\Post(
     *     path="/telephony/api/cucm/site/migration/summary",
     *     tags={"Management - CUCM - Site Migration"},
     *     summary="Create New Site in CUCM",
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
     *     @SWG\Parameter(
     *         name="npa",
     *         in="formData",
     *         description="NPA (###) - Area Code",
     *         required=false,
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
    $api->post('cucm/site/migration/summary', 'App\Http\Controllers\CucmSiteMigration@migrationSiteSummary');

    /********************************
       Run Migration routes
    ********************************/
    /**
     * @SWG\Post(
     *     path="/telephony/api/cucm/site/migration/run",
     *     tags={"Management - CUCM - Site Migration"},
     *     summary="Run Migration Plan",
     *     description="Run Migration",
     *     operationId="createSite",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="type",
     *         in="formData",
     *         description="Add, Update, Delete",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="migration",
     *         in="formData",
     *         description="Array of Migration Ojbects",
     *         required=true,
     *         type="array",
     *         @SWG\Items(
     *             type="string",
     *             description="cucm objects",
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
    $api->post('cucm/site/migration/run', 'App\Http\Controllers\CucmSiteMigration@run_migration');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/site/migration/phonescan",
     *     tags={"Management - CUCM - Site Migration"},
     *     summary="Rescan Site Phones and update DB",
     *     description="",
     *     operationId="scanphones",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="sitecode",
     *         in="path",
     *         description="Name of Site",
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
    $api->get('cucm/site/migration/phonescan', 'App\Http\Controllers\CucmSiteMigration@rescan_site_phones');

    /**
     * @SWG\Post(
     *     path="/telephony/api/cucm/site/rename_site",
     *     tags={"Management - CUCM - Site Migration"},
     *     summary="Update Site Plan by ID for authorized user",
     *     description="Rename Site Ojbects to new Sitecode",
     *     operationId="renameSite",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="sitecode",
     *         in="formData",
     *         description="Sitecode",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="newsitecode",
     *         in="formData",
     *         description="New Sitecode",
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
    $api->post('/cucm/site/rename_site', 'App\Http\Controllers\CucmSiteMigration@rename_site');

    /**
     * @SWG\Delete(
     *     path="/telephony/api/cucm/site/delete/{sitecode}",
     *     tags={"Management - CUCM - Site Migration"},
     *     summary="Delete Site from CUCM",
     *     description="
     * WARNING!!! THIS COMPLETELY DELETES THE SITE!!! THERE IS NO RESTORE!!!
     * This does exclude phones. All Site Phones must be deleted first.",
     *     operationId="deleteSite",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="sitecode",
     *         in="path",
     *         description="Name of Site - WARNING!!! THIS COMPLETELY DELETES THE SITE!!!",
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
    $api->delete('cucm/site/delete/{sitecode}', 'App\Http\Controllers\CucmSiteMigration@delete_site');
