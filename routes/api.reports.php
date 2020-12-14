<?php

    /**
     * @SWG\Get(
     *     path="/telephony/api/reports",
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
