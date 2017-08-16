<?php


    /********************************
       CUCM Reports API
    ********************************/

    /**
     * @SWG\Get(
     *     path="/telephony/api/reports/sites",
     *     tags={"CUCM Reports"},
     *     summary="List of DID Blocks for authorized user",
     *     description="",
     *     operationId="listSites",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *
     *     ),
     * )
     **/
    $api->get('/reports/sites', 'App\Http\Controllers\CucmReportsController@sitesSummary');

    /**
     * @SWG\Get(
     *     path="/telephony/api/reports/site/{sitecode}",
     *     tags={"CUCM Reports"},
     *     summary="List of Site Objects",
     *     description="",
     *     operationId="listSites",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="sitecode",
     *         in="path",
     *         description="sitecode",
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
    $api->get('/reports/site/{sitecode}', 'App\Http\Controllers\CucmReportsController@siteSummary');

    /**
     * @SWG\Get(
     *     path="/telephony/api/reports/site/{sitecode}/erl/discrepancies",
     *     tags={"CUCM Reports"},
     *     summary="List of phones physically at the site but are not configured for site. ",
     *     description="",
     *     operationId="listSites",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="sitecode",
     *         in="path",
     *         description="sitecode",
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
    $api->get('/reports/site/{sitecode}/erl/discrepancies', 'App\Http\Controllers\CucmReportsController@phones_in_site_erl_but_not_in_site_config');

    /**
     * @SWG\Get(
     *     path="/telephony/api/reports/phones/{sitecode}",
     *     tags={"CUCM Reports"},
     *     summary="List of Phones for Site",
     *     description="",
     *     operationId="listSites",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="sitecode",
     *         in="path",
     *         description="sitecode",
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
    $api->get('/reports/phones/{sitecode}', 'App\Http\Controllers\CucmReportsController@sitePhones');

    /**
     * @SWG\Get(
     *     path="/telephony/api/reports/siteE911TrunkingReport",
     *     tags={"CUCM Reports"},
     *     summary="List Report of Trunking and E911 for all Sites",
     *     description="",
     *     operationId="listSites",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *
     *     ),
     * )
     **/
    $api->get('/reports/siteE911TrunkingReport', 'App\Http\Controllers\CucmReportsController@siteE911TrunkingReport');

    /**
     * @SWG\Get(
     *     path="/telephony/api/reports/get_phone_models_inuse",
     *     tags={"CUCM Reports"},
     *     summary="List of Phone Models in Use",
     *     description="",
     *     operationId="listPhoneModels",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *
     *     ),
     * )
     **/
    $api->get('/reports/get_phone_models_inuse', 'App\Http\Controllers\CucmReportsController@get_phone_models_inuse');

    /**
     * @SWG\Get(
     *     path="/telephony/api/reports/get_count_phone_models_inuse",
     *     tags={"CUCM Reports"},
     *     summary="List Count of Phone Models in Use",
     *     description="",
     *     operationId="listPhoneModels",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *
     *     ),
     * )
     **/
    $api->get('/reports/get_count_phone_models_inuse', 'App\Http\Controllers\CucmReportsController@get_count_phone_models_inuse');

    /**
     * @SWG\Get(
     *     path="/telephony/api/reports/linecleanup",
     *     tags={"CUCM Reports"},
     *     summary="List of DID Blocks for authorized user",
     *     description="",
     *     operationId="listSites",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *
     *     ),
     * )
     **/
    $api->get('/reports/linecleanup', 'App\Http\Controllers\CucmReportsController@get_line_cleanup_report');
