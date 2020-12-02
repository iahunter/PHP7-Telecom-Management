<?php

    /**
     * @SWG\Get(
     *     path="/telephony/api/gizmo/teams/number/{countrycode}/{number}",
     *     tags={"Management - Gizmo - Teams - User Provisioning"},
     *     summary="Get Teams User Info from Number",
     *     description="",
     *     operationId="getUserbyNumber",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="countrycode",
     *         in="path",
     *         description="Country Code - US: 1",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="number",
     *         in="path",
     *         description="Phone Number",
     *         required=true,
     *         type="string"
     *     ),
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
    $api->get('gizmo/teams/number/{countrycode}/{number}', 'App\Http\Controllers\GizmoController@getTeamsUserbyNumber');

    /**
     * @SWG\Get(
     *     path="/telephony/api/gizmo/teams/allvoiceusers",
     *     tags={"Management - Gizmo - Teams - User Provisioning"},
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
    $api->get('gizmo/teams/allvoiceusers', 'App\Http\Controllers\GizmoController@getAllTeamsVoiceUsers');
