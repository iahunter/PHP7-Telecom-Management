<?php

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/ctiroutepoint/{name}",
     *     tags={"Management - CUCM - CTI Route Point Provisioning"},
     *     summary="Get CTI Route Point Details by Name",
     *     description="",
     *     operationId="getCTIRoutePoint",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="name",
     *         in="path",
     *         description="Name of CTI RoutePoint - Example OnCall-RP",
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
    $api->get('cucm/ctiroutepoint/{name}', 'App\Http\Controllers\CucmCtiRoutePoint@getCtiRoutePoint');

    /**
     * @SWG\Delete(
     *     path="/telephony/api/cucm/ctiroutepoint/{name}",
     *     tags={"Management - CUCM - CTI Route Point Provisioning"},
     *     summary="Remove CTI Route Point by Name",
     *     description="",
     *     operationId="deleteCTIRoutePoint",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="name",
     *         in="path",
     *         description="Name of CTI Route Point to Delete - Example SEP0004DEADBEEF",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",

     *     ),
     * )
     **/
    $api->delete('cucm/ctiroutepoint/{name}', 'App\Http\Controllers\CucmCtiRoutePoint@deleteCtiRoutePointbyName');

    /**
     * @SWG\Post(
     *     path="/telephony/api/cucm/ctiroutepoint",
     *     tags={"Management - CUCM - CTI Route Point Provisioning"},
     *     summary="Create New CTI Route Point in CUCM",
     *     description="",
     *     operationId="createCTIRoutePoint",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="sitecode",
     *         in="formData",
     *         description="Sitecode",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         in="formData",
     *         description="CTI Route Point Name - Example 0004DEADBEEF",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="firstname",
     *         in="formData",
     *         description="First Name - John",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="lastname",
     *         in="formData",
     *         description="Last Name - Doe",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="username",
     *         in="formData",
     *         description="User Name - John.Doe, CallManager.Unassign",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="dn",
     *         in="formData",
     *         description="Directory Number - Example: 4025551234",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="extlength",
     *         in="formData",
     *         description="Internal Extension Length - 4 digit is standard - Used for Internal Short Dialing",
     *		   enum={"4", "5", "10"},
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="language",
     *         in="formData",
     *         description="Language",
     *		   enum={"English", "French"},
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="voicemail",
     *         in="formData",
     *         description="Does user require a Voicemail Box with this DN?",
     *		   enum={"true", "false"},
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="notes",
     *         in="formData",
     *         description="Notes",
     *         required=false,
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
    $api->post('cucm/ctiroutepoint', 'App\Http\Controllers\CucmCtiRoutePoint@createCtiRoutePoint');
