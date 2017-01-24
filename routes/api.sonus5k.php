<?php

    /**
     * @SWG\Get(
     *     path="/telephony/api/sonus/activecalls",
     *     tags={"Management - Sonus - Monitoring"},
     *     summary="List Active Calls",
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
    $api->get('sonus/activecalls', 'App\Http\Controllers\Sonus5kcontroller@listactivecalls');
