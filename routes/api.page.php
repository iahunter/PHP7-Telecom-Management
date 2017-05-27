<?php


    /**
     * @SWG\Get(
     *     path="/telephony/api/page/request/{name}",
     *     tags={"Logging and Permissions"},
     *     summary="Page Request Log",
     *     description="",
     *     operationId="",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="name",
     *         in="path",
     *         description="Hostname or IP Address",
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
    $api->get('/page/request/{name}', 'App\Http\Controllers\LogController@log_page_name');

    /**
     * @SWG\Get(
     *     path="/telephony/api/page/test",
     *     tags={"Logging and Permissions"},
     *     summary="TEST",
     *     description="",
     *     operationId="",
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

    /**
     * @SWG\Get(
     *     path="/telephony/api/page/permissions",
     *     tags={"Logging and Permissions"},
     *     summary="Page View Permissions",
     *     description="Get my page viewing permissions",
     *     operationId="",
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
    $api->get('/page/permissions', 'App\Http\Controllers\LogController@permissions');

    /**
     * @SWG\Get(
     *     path="/telephony/api/activitylogs/last24hrs",
     *     tags={"Logging and Permissions"},
     *     summary="Get Logs from last 24hrs",
     *     description="",
     *     operationId="",
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
    $api->get('/activitylogs/last24hrs', 'App\Http\Controllers\LogController@get_last24hrs_logs');

    /**
     * @SWG\Get(
     *     path="/telephony/api/activitylogs/pagelogs/last24hrs",
     *     tags={"Logging and Permissions"},
     *     summary="Get Page Logs from last 24hrs",
     *     description="",
     *     operationId="",
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
    $api->get('/activitylogs/pagelogs/last24hrs', 'App\Http\Controllers\LogController@get_last24hrs_page_logs');

    /**
     * @SWG\Post(
     *     path="/telephony/api/activitylogs/bydates",
     *     tags={"Logging and Permissions"},
     *     summary="Get Logs from Dates",
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
    $api->post('/activitylogs/bydates', 'App\Http\Controllers\LogController@get_logs_by_date_range');
