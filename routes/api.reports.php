<?php

    /**
     * @SWG\Get(
     *     path="/api/reports",
     *     tags={"Reports - Custom"},
     *     summary="Get List of Reports",
     *     description="",
     *     operationId="listReportTypes",
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
    $api->get('reports', 'App\Http\Controllers\ReportsController@listReportTypes');

    /**
     * @SWG\Get(
     *     path="/api/reports/type/{type}",
     *     tags={"Reports - Custom"},
     *     summary="Get List of Reports",
     *     description="",
     *     operationId="listSites",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="type",
     *         in="path",
     *         description="Report Type",
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
    $api->get('/reports/type/{type}', 'App\Http\Controllers\ReportsController@getReport');
