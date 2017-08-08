<?php

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucmris/getphone/ip/{name}",
     *     tags={"Management - CUCM RIS - API"},
     *     summary="Get Phone IP",
     *     description="",
     *     operationId="",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="name",
     *         in="path",
     *         description="Name of Phone",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->get('cucmris/getphone/ip/{name}', 'App\Http\Controllers\CucmRealTimeController@get_phone_ip');
