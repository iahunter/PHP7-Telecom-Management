<?php

    /**
     * @SWG\Get(
     *     path="/telephony/api/reports/teams/allvoiceusers",
     *     tags={"Reports - Microsoft Teams"},
     *     summary="Get All Teams Voice Users",
     *     description="",
     *     operationId="getUserbyNumber",
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
    $api->get('reports/teams/allvoiceusers', 'App\Http\Controllers\TeamsReportsController@getAllTeamsVoiceUsers');
