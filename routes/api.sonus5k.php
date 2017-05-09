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

    /**
     * @SWG\Get(
     *     path="/telephony/api/sonus/cdrs",
     *     tags={"Management - Sonus - Monitoring"},
     *     summary="Get CDRs",
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
    $api->get('sonus/cdrs', 'App\Http\Controllers\Sonus5kcontroller@getcdrs');

    /**
     * @SWG\Get(
     *     path="/telephony/api/sonus/2day_call_summary",
     *     tags={"Management - Sonus - Monitoring"},
     *     summary="Get CDRs for last two days in travis view format",
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
    $api->get('sonus/2day_call_summary', 'App\Http\Controllers\Sonus5kcontroller@get_last_two_days_cdr_summary');

    /**
     * @SWG\Get(
     *     path="/telephony/api/sonus/2day_completed_call_summary",
     *     tags={"Management - Sonus - Monitoring"},
     *     summary="Get CDRs for last 2 days",
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
    $api->get('sonus/2day_completed_call_summary', 'App\Http\Controllers\Sonus5kcontroller@get_last_two_days_cdr_completed_call_summary');

    /**
     * @SWG\Get(
     *     path="/telephony/api/sonus/2day_completed_call_summary_bad_calls",
     *     tags={"Management - Sonus - Monitoring"},
     *     summary="Get CDRs for last two days in travis view format",
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
    $api->get('sonus/2day_completed_call_summary_bad_calls', 'App\Http\Controllers\Sonus5kcontroller@get_last_two_days_cdr_completed_call_summary_packetloss');
