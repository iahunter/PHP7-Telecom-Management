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
	
	
	
	/**
     * @SWG\Get(
     *     path="/telephony/api/sonus/backups",
     *     tags={"Management - Sonus - Monitoring"},
     *     summary="Backup Configs",
     *     description="",
     *     operationId="backups",
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
    $api->get('sonus/backups', 'App\Http\Controllers\Sonus5kcontroller@compareconfigs');

