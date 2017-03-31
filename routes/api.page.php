<?php

    /**
     * @SWG\Get(
     *     path="/telephony/api/page/request/{name}",
     *     tags={"Logging"},
     *     summary="Page Request Log",
     *     description="",
     *     operationId="getActiveCalls",
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
    $api->get('/page/request/{name}', 'App\Http\Controllers\LogController@log_page_name');

    /**
     * @SWG\Get(
     *     path="/telephony/api/page/test",
     *     tags={"Logging"},
     *     summary="TEST",
     *     description="",
     *     operationId="getActiveCalls",
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
    $api->get('/page/test', 'App\Http\Controllers\LogController@test');
