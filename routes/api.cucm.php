<?php


    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/sites",
     *     tags={"VoIP - CUCM"},
     *     summary="Get List of Sites from CUCM Device Pools",
     *     description="",
     *     operationId="getDidblock",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->get('cucm/sites', 'App\Http\Controllers\Cucm@listsites');
