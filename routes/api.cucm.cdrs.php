<?php


    /**
     * @SWG\Get(
     *     path="/telephony/api/cucmcdrs/search/{column}/{search}",
     *     tags={"CDR - CUCM History"},
     *     summary="List CDR Records by Date Range",
     *     description="",
     *     operationId="searchCDR",
     *     consumes={"application/json"},
     *     produces={"application/json"},
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
     *         description="Search",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->get('cucmcdrs/search/{column}/{search}', 'App\Http\Controllers\CucmCdrCmrController@searchCDR');

    /**
     * @SWG\Post(
     *     path="/telephony/api/cucmcdrs/callsbydaterange",
     *     tags={"CDR - CUCM History"},
     *     summary="List CDR Records by Date Range",
     *     description="",
     *     operationId="list_calls_by_date_range",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *	   @SWG\Parameter(
     *         name="start",
     *         in="formData",
     *         description="Example: 5/10/2017",
     *         required=true,
     *         type="string"
     *     ),
     *	   @SWG\Parameter(
     *         name="end",
     *         in="formData",
     *         description="Example: 5/10/2017",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->post('cucmcdrs/callsbydaterange', 'App\Http\Controllers\CucmCdrCmrController@list_calls_by_date_range');

    /**
     * @SWG\Post(
     *     path="/telephony/api/cucmcdrs/calls_with_loss_by_daterange",
     *     tags={"CDR - CUCM History"},
     *     summary="List CDR Records by Date Range",
     *     description="",
     *     operationId="list_calls_by_date_range",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *	   @SWG\Parameter(
     *         name="start",
     *         in="formData",
     *         description="Example: 5/10/2017",
     *         required=true,
     *         type="string"
     *     ),
     *	   @SWG\Parameter(
     *         name="end",
     *         in="formData",
     *         description="Example: 5/10/2017",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->post('cucmcdrs/calls_with_loss_by_daterange', 'App\Http\Controllers\CucmCdrCmrController@list_calls_by_date_range_with_loss');

    /**
     * @SWG\Post(
     *     path="/telephony/api/cucmcdrs/list_last_24hr_calls_by_number_search",
     *     tags={"CDR - CUCM History"},
     *     summary="List Last 24hr CDR Records by Number",
     *     description="",
     *     operationId="list_calls_by_date_range",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *	   @SWG\Parameter(
     *         name="search",
     *         in="formData",
     *         description="Number to Search by",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->post('cucmcdrs/list_last_24hr_calls_by_number_search', 'App\Http\Controllers\CucmCdrCmrController@list_last_24hr_calls_by_number_search');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucmcdrs/list_todays_calls_with_loss",
     *     tags={"CDR - CUCM History"},
     *     summary="Get Todays CDRs with Packet Loss",
     *     description="",
     *     operationId="backups",
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
    $api->get('cucmcdrs/list_todays_calls_with_loss', 'App\Http\Controllers\CucmCdrCmrController@list_todays_calls_with_loss');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucmcdrs/list_todays_attempts",
     *     tags={"CDR - CUCM History"},
     *     summary="Get Todays CDR Attmetps",
     *     description="",
     *     operationId="attempts",
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
    $api->get('cucmcdrs/list_todays_attempts', 'App\Http\Controllers\CucmCdrCmrController@list_todays_attempts');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucmcdrs/list_todays_attempts_summary_report",
     *     tags={"CDR - CUCM History"},
     *     summary="Get Todays CDR Attmetps",
     *     description="",
     *     operationId="attempts",
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
    $api->get('cucmcdrs/list_todays_attempts_summary_report', 'App\Http\Controllers\CucmCdrCmrController@list_todays_attempts_summary_report');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucmcdrs/list_todays_pkt_loss_summary_report",
     *     tags={"CDR - CUCM History"},
     *     summary="Get Todays CDR Calls with 1% Loss",
     *     description="",
     *     operationId="attempts",
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
    $api->get('cucmcdrs/list_todays_pkt_loss_summary_report', 'App\Http\Controllers\CucmCdrCmrController@list_todays_pkt_loss_summary_report');
