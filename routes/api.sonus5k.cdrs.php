<?php

    /**
     * @SWG\Post(
     *     path="/telephony/api/sonus5kcdrs/callsbydaterange",
     *     tags={"CDR - Sonus History"},
     *     summary="List CDR Records by Date Range",
     *     description="",
     *     operationId="list_calls_by_date_range",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *	   @SWG\Parameter(
     *         name="start",
     *         in="formData",
     *         description="Example: 5/10/2017",
     *         required=true,
     *         type="string"
     *     ),
     *	   @SWG\Parameter(
     *         name="end",
     *         in="formData",
     *         description="Example: 5/10/2017",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->post('sonus5kcdrs/callsbydaterange', 'App\Http\Controllers\Sonus5kCDRcontroller@list_calls_by_date_range');

    /**
     * @SWG\Post(
     *     path="/telephony/api/sonus5kcdrs/calls_with_loss_by_daterange",
     *     tags={"CDR - Sonus History"},
     *     summary="List CDR Records by Date Range",
     *     description="",
     *     operationId="list_calls_by_date_range",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *	   @SWG\Parameter(
     *         name="start",
     *         in="formData",
     *         description="Example: 5/10/2017",
     *         required=true,
     *         type="string"
     *     ),
     *	   @SWG\Parameter(
     *         name="end",
     *         in="formData",
     *         description="Example: 5/10/2017",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->post('sonus5kcdrs/calls_with_loss_by_daterange', 'App\Http\Controllers\Sonus5kCDRcontroller@list_calls_by_date_range_with_loss');

    /**
     * @SWG\Get(
     *     path="/telephony/api/sonus/cdrs",
     *     tags={"Management - Sonus - Onbox CDRs"},
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
    $api->get('sonus/cdrs', 'App\Http\Controllers\Sonus5kCDRcontroller@getcdrs');

    /**
     * @SWG\Get(
     *     path="/telephony/api/sonus/2day_call_summary",
     *     tags={"Management - Sonus - Onbox CDRs"},
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
    $api->get('sonus/2day_call_summary', 'App\Http\Controllers\Sonus5kCDRcontroller@get_last_two_days_cdr_summary');

    /**
     * @SWG\Get(
     *     path="/telephony/api/sonus/2day_completed_call_summary",
     *     tags={"Management - Sonus - Onbox CDRs"},
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
    $api->get('sonus/2day_completed_call_summary', 'App\Http\Controllers\Sonus5kCDRcontroller@get_last_two_days_cdr_completed_call_summary');

    /**
     * @SWG\Get(
     *     path="/telephony/api/sonus/2day_completed_call_summary_bad_calls",
     *     tags={"Management - Sonus - Onbox CDRs"},
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
    $api->get('sonus/2day_completed_call_summary_bad_calls', 'App\Http\Controllers\Sonus5kCDRcontroller@get_last_two_days_cdr_completed_call_summary_packetloss');
