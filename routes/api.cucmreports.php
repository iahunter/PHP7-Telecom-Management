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

     *     ),
     * )
     **/
    $api->get('/reports/sites', 'App\Http\Controllers\CucmReportsController@sitesSummary');
	
	/**
     * @SWG\Get(
     *     path="/telephony/api/reports/site/{sitecode}",
     *     tags={"CUCM Reports"},
     *     summary="List of DID Blocks for authorized user",
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

     *     ),
     * )
     **/
    $api->get('/reports/site/{sitecode}', 'App\Http\Controllers\CucmReportsController@siteSummary');
	
	
	/**
     * @SWG\Get(
     *     path="/telephony/api/reports/phones/{sitecode}",
     *     tags={"CUCM Reports"},
     *     summary="List of DID Blocks for authorized user",
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

     *     ),
     * )
     **/
    $api->get('/reports/phones/{sitecode}', 'App\Http\Controllers\CucmReportsController@sitePhones');
	

    /**
     * @SWG\Get(
     *     path="/telephony/api/reports/siteE911TrunkingReport",
     *     tags={"CUCM Reports"},
     *     summary="List of DID Blocks for authorized user",
     *     description="",
     *     operationId="listSites",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",

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

     *     ),
     * )
     **/
    $api->get('/reports/get_count_phone_models_inuse', 'App\Http\Controllers\CucmReportsController@get_count_phone_models_inuse');
