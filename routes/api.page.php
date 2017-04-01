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
