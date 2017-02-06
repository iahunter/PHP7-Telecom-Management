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

