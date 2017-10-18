<?php

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/gatewaycalls/listcallstats",
     *     tags={"Calls"},
     *     summary="List Call Stats",
     *     description="",
     *     operationId="listcallstats",
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
    $api->get('/cucm/gatewaycalls/listcallstats', 'App\Http\Controllers\GatewayCallsController@listcallstats');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/gatewaycalls/dayscallstats",
     *     tags={"Calls"},
     *     summary="List Call Stats",
     *     description="",
     *     operationId="listcallstats",
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
    $api->get('/cucm/gatewaycalls/dayscallstats', 'App\Http\Controllers\GatewayCallsController@list_last_24hrs_callstats');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/gatewaycalls/weekscallstats",
     *     tags={"Calls"},
     *     summary="List Call Stats",
     *     description="",
     *     operationId="listcallstats",
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
    $api->get('/cucm/gatewaycalls/weekscallstats', 'App\Http\Controllers\GatewayCallsController@list_last_7days_callstats');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/gatewaycalls/monthcallstats",
     *     tags={"Calls"},
     *     summary="List Month Call Stats",
     *     description="",
     *     operationId="listmonthscallstats",
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
    $api->get('/cucm/gatewaycalls/monthcallstats', 'App\Http\Controllers\GatewayCallsController@list_last_month_callstats');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/gatewaycalls/monthdailypeakcallstats",
     *     tags={"Calls"},
     *     summary="List 1 Month Daily Call Peak Stats",
     *     description="",
     *     operationId="listmonthscallstats",
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
    $api->get('/cucm/gatewaycalls/monthdailypeakcallstats', 'App\Http\Controllers\GatewayCallsController@list_last_month_daily_call_peak_stats');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/gatewaycalls/threemonthdailypeakcallstats",
     *     tags={"Calls"},
     *     summary="List 3 Month Daily Call Peak Stats",
     *     description="",
     *     operationId="listmonthscallstats",
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
    $api->get('/cucm/gatewaycalls/threemonthdailypeakcallstats', 'App\Http\Controllers\GatewayCallsController@list_3_month_daily_call_peak_stats');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/gatewaycalls/threemonthdailypeakcallstats_sql",
     *     tags={"Calls"},
     *     summary="List 3 Month Daily Call Peak Stats - Single SQL Query",
     *     description="",
     *     operationId="listmonthscallstats",
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
    $api->get('/cucm/gatewaycalls/threemonthdailypeakcallstats_sql', 'App\Http\Controllers\GatewayCallsController@list_3_month_daily_call_peak_stats_sql');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/gatewaycalls/oneyeardailypeakcallstats_sql",
     *     tags={"Calls"},
     *     summary="List 1 Year Daily Call Peak Stats - Single SQL Query",
     *     description="",
     *     operationId="listmonthscallstats",
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
    $api->get('/cucm/gatewaycalls/oneyeardailypeakcallstats_sql', 'App\Http\Controllers\GatewayCallsController@list_one_year_daily_call_peak_stats_sql');

    /**
     * @SWG\Post(
     *     path="/telephony/api/cucm/gatewaycalls/callsbydaterange",
     *     tags={"Calls"},
     *     summary="List CDR Records by Date Range",
     *     description="",
     *     operationId="list_callstats_by_date_range",
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
    $api->post('/cucm/gatewaycalls/callsbydaterange', 'App\Http\Controllers\GatewayCallsController@list_callstats_by_date_range');
