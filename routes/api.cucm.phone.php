<?php

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/phone/{name}",
     *     tags={"Management - CUCM - Phone Provisioning"},
     *     summary="Get Phone Details by Name",
     *     description="",
     *     operationId="getPhone",
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
    $api->get('cucm/phone/{name}', 'App\Http\Controllers\Cucmphone@getPhone');
