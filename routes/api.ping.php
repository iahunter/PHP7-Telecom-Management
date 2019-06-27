<?php

    /**
     * @SWG\Get(
     *     path="/telephony/api/pinghost/{host}",
     *     tags={"Monitoring"},
     *     summary="Ping Host",
     *     description="",
     *     operationId="",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="host",
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
    $api->get('/pinghost/{host}', 'App\Http\Controllers\PingController@pinghost');
