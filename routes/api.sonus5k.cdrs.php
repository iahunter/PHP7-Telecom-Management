<?php


    /**
     * @SWG\Get(
     *     path="/telephony/api/sonus5kcdrs/search/{column}/{search}",
     *     tags={"CDR - Sonus History"},
     *     summary="List CDR Records by Date Range",
     *     description="",
     *     operationId="searchCDR",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="column",
     *         in="path",
     *         description="Column to Search",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="search",
     *         in="path",
     *         description="Search",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->get('sonus5kcdrs/search/{column}/{search}', 'App\Http\Controllers\Sonus5kCDRcontroller@searchCDR');

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
     *     path="/telephony/api/sonus/list_todays_calls_with_loss",
     *     tags={"CDR - Sonus History"},
     *     summary="Get Todays CDRs with Packet Loss",
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
    $api->get('sonus/list_todays_calls_with_loss', 'App\Http\Controllers\Sonus5kCDRcontroller@list_todays_calls_with_loss');

    /**
     * @SWG\Get(
     *     path="/telephony/api/sonus/list_todays_attempts",
     *     tags={"CDR - Sonus History"},
     *     summary="Get Todays CDR Attmetps",
     *     description="",
     *     operationId="attempts",
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
    $api->get('sonus/list_todays_attempts', 'App\Http\Controllers\Sonus5kCDRcontroller@list_todays_attempts');

    /**
     * @SWG\Get(
     *     path="/telephony/api/sonus/list_todays_attempts_summary_report",
     *     tags={"CDR - Sonus History"},
     *     summary="Get Todays CDR Attmetps",
     *     description="",
     *     operationId="attempts",
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
    $api->get('sonus/list_todays_attempts_summary_report', 'App\Http\Controllers\Sonus5kCDRcontroller@list_todays_attempts_summary_report');

    /**
     * @SWG\Get(
     *     path="/telephony/api/sonus/list_last_hour_top_attempt_counts_by_called_number_report",
     *     tags={"CDR - Sonus History"},
     *     summary="Get Last Hour Top Attempts by Called Nubmer Report",
     *     description="",
     *     operationId="attempts",
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
    $api->get('sonus/list_last_hour_top_attempt_counts_by_called_number_report', 'App\Http\Controllers\Sonus5kCDRcontroller@list_last_hour_top_attempt_counts_by_called_number_report');

    /**
     * @SWG\Get(
     *     path="/telephony/api/sonus/list_last_hour_top_attempt_counts_by_calling_number_report",
     *     tags={"CDR - Sonus History"},
     *     summary="Get Last Hour Top Attempts by Calling Nubmer Report",
     *     description="",
     *     operationId="attempts",
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
    $api->get('sonus/list_last_hour_top_attempt_counts_by_calling_number_report', 'App\Http\Controllers\Sonus5kCDRcontroller@list_last_hour_top_attempt_counts_by_calling_number_report');

    /**
     * @SWG\Get(
     *     path="/telephony/api/sonus/list_todays_top_attempt_counts_by_called_number_report",
     *     tags={"CDR - Sonus History"},
     *     summary="Get Todays Top Attempts by Called Number Report",
     *     description="",
     *     operationId="attempts",
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
    $api->get('sonus/list_todays_top_attempt_counts_by_called_number_report', 'App\Http\Controllers\Sonus5kCDRcontroller@list_todays_top_attempt_counts_by_called_number_report');

    /**
     * @SWG\Get(
     *     path="/telephony/api/sonus/list_todays_top_attempt_counts_by_calling_number_report",
     *     tags={"CDR - Sonus History"},
     *     summary="Get Todays Top Attempts by Calling Number Report",
     *     description="",
     *     operationId="attempts",
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
    $api->get('sonus/list_todays_top_attempt_counts_by_calling_number_report', 'App\Http\Controllers\Sonus5kCDRcontroller@list_todays_top_attempt_counts_by_calling_number_report');

    /**
     * @SWG\Get(
     *     path="/telephony/api/sonus/list_todays_pkt_loss_summary_report",
     *     tags={"CDR - Sonus History"},
     *     summary="Get Todays CDR Calls with 1% Loss",
     *     description="",
     *     operationId="attempts",
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
    $api->get('sonus/list_todays_pkt_loss_summary_report', 'App\Http\Controllers\Sonus5kCDRcontroller@list_todays_pkt_loss_summary_report');

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

    /**
     * @SWG\Get(
     *     path="/telephony/api/sonus/call_termination_code/{code}",
     *     tags={"Management - Sonus - Onbox CDRs"},
     *     summary="Lookup Sonus Call Termination Reason Codes by ID",
     *     description="",
     *     operationId="getterminationcode",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="code",
     *         in="path",
     *         description="Call Termination Code ID",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->get('sonus/call_termination_code/{code}', 'App\Http\Controllers\Sonus5kCDRcontroller@get_call_termination_code');

    /**
     * @SWG\Get(
     *     path="/telephony/api/sonus/disconnect_initiator_code/{code}",
     *     tags={"Management - Sonus - Onbox CDRs"},
     *     summary="Lookup Sonus Disconnect Initiator Code by ID",
     *     description="",
     *     operationId="getterminationcode",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="code",
     *         in="path",
     *         description="Call Termination Code ID",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->get('sonus/disconnect_initiator_code/{code}', 'App\Http\Controllers\Sonus5kCDRcontroller@get_disconnect_initiator_code');
