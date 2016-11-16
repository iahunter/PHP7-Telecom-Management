<?php

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/css",
     *     tags={"Management - CUCM"},
     *     summary="List Css details",
     *     description="",
     *     operationId="getCssDetails",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->get('cucm/css', 'App\Http\Controllers\Cucm@listCssDetails');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/css/{name}",
     *     tags={"Management - CUCM"},
     *     summary="List Css details by Name",
     *     description="",
     *     operationId="getCssbyName",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="name",
     *         in="path",
     *         description="Name of Css",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->get('cucm/css/{name}', 'App\Http\Controllers\Cucm@listCssDetailsbyName');

