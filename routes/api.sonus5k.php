<?php

	/**
     * @SWG\Get(
     *     path="/telephony/api/sonus/activecallcounts",
     *     tags={"Management - Sonus - Monitoring"},
     *     summary="List Active Call Counts",
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
    $api->get('sonus/activecallcounts', 'App\Http\Controllers\Sonus5kcontroller@getactivecallcounts');

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

    /**
     * @SWG\Get(
     *     path="/telephony/api/sonus/listcallDetailStatus",
     *     tags={"Management - Sonus - Monitoring"},
     *     summary="List Active Call Details",
     *     description="",
     *     operationId="getcallDetailStatus",
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
    $api->get('sonus/listcallDetailStatus', 'App\Http\Controllers\Sonus5kcontroller@listcallDetailStatus');

    /**
     * @SWG\Get(
     *     path="/telephony/api/sonus/listcallMediaStatus",
     *     tags={"Management - Sonus - Monitoring"},
     *     summary="List Active Call Media Details",
     *     description="",
     *     operationId="listcallMediaStatus",
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
    $api->get('sonus/listcallMediaStatus', 'App\Http\Controllers\Sonus5kcontroller@listcallMediaStatus');

    /**
     * @SWG\Get(
     *     path="/telephony/api/sonus/listcallDetailStatus_Media",
     *     tags={"Management - Sonus - Monitoring"},
     *     summary="List Active Call Media Details",
     *     description="",
     *     operationId="listcallMediaStatus",
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
    $api->get('sonus/listcallDetailStatus_Media', 'App\Http\Controllers\Sonus5kcontroller@listcallDetailStatus_Media');

    /**
     * @SWG\Get(
     *     path="/telephony/api/sonus/activealarms",
     *     tags={"Management - Sonus - Monitoring"},
     *     summary="List Active Alarms",
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
    $api->get('sonus/activealarms', 'App\Http\Controllers\Sonus5kcontroller@listactivealarms');
