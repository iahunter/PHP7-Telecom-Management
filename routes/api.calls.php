<?php

    /**
     * @SWG\Get(
     *     path="/telephony/api/calls/listcallstats",
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
     *     path="/telephony/api/calls/dayscallstats",
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
     *     path="/telephony/api/calls/weekscallstats",
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
     * @SWG\Post(
     *     path="/telephony/api/calls/callsbydaterange",
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
