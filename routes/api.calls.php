<?php

    /**
     * @SWG\Get(
     *     path="/api/calls/listcallstats",
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
    $api->get('/calls/listcallstats', 'App\Http\Controllers\Callcontroller@listcallstats');

    /**
     * @SWG\Get(
     *     path="/api/calls/dayscallstats",
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
    $api->get('/calls/dayscallstats', 'App\Http\Controllers\Callcontroller@list_last_24hrs_callstats');

    /**
     * @SWG\Get(
     *     path="/api/calls/weekscallstats",
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
    $api->get('/calls/weekscallstats', 'App\Http\Controllers\Callcontroller@list_last_7days_callstats');

    /**
     * @SWG\Get(
     *     path="/api/calls/monthcallstats",
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
    $api->get('/calls/monthcallstats', 'App\Http\Controllers\Callcontroller@list_last_month_callstats');

    /**
     * @SWG\Get(
     *     path="/api/calls/monthdailypeakcallstats",
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
    $api->get('/calls/monthdailypeakcallstats', 'App\Http\Controllers\Callcontroller@list_last_month_daily_call_peak_stats');

    /**
     * @SWG\Get(
     *     path="/api/calls/threemonthdailypeakcallstats",
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
    $api->get('/calls/threemonthdailypeakcallstats', 'App\Http\Controllers\Callcontroller@list_3_month_daily_call_peak_stats');

    /**
     * @SWG\Get(
     *     path="/api/calls/threemonthdailypeakcallstats_sql",
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
    $api->get('/calls/threemonthdailypeakcallstats_sql', 'App\Http\Controllers\Callcontroller@list_3_month_daily_call_peak_stats_sql');

    /**
     * @SWG\Get(
     *     path="/api/calls/oneyeardailypeakcallstats_sql",
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
    $api->get('/calls/oneyeardailypeakcallstats_sql', 'App\Http\Controllers\Callcontroller@list_one_year_daily_call_peak_stats_sql');

    /**
     * @SWG\Post(
     *     path="/api/calls/callsbydaterange",
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
    $api->post('/calls/callsbydaterange', 'App\Http\Controllers\Callcontroller@list_callstats_by_date_range');
