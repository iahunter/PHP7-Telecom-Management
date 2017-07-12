<?php

    /**
     * @SWG\Get(
     *     path="/telephony/api/cucm/line/{partition}/{pattern}",
     *     tags={"Management - CUCM - Line - Provisioning"},
     *     summary="Get Line Details by Partition and Pattern",
     *     description="",
     *     operationId="getLine",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="partition",
     *         in="path",
     *         description="Partition name",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="pattern",
     *         in="path",
     *         description="Pattern name - Example 10 Digit number",
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
    $api->get('cucm/line/{partition}/{pattern}', 'App\Http\Controllers\CucmLine@getLineCFWAbyPattern');

    /**
     * @SWG\Put(
     *     path="/telephony/api/cucm/line/cfa",
     *     tags={"Management - CUCM - Line - Provisioning"},
     *     summary="Update Line CFA in CUCM",
     *     description="",
     *     operationId="updateLineCFWA",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="pattern",
     *         in="formData",
     *         description="10 Digit Number",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="partition",
     *         in="formData",
     *         description="Example: Global-All-Lines - Default for all Lines",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="sitecode",
     *         in="formData",
     *         description="Sitecode being used",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="cfa_destination",
     *         in="formData",
     *         description="Call Forward Destination - 10 Digit number only",
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
    $api->put('cucm/line/cfa', 'App\Http\Controllers\CucmLine@updateLineCFWAbyPattern');

    /**
     * @SWG\Put(
     *     path="/telephony/api/cucm/line",
     *     tags={"Management - CUCM - Line - Provisioning"},
     *     summary="Update Line in CUCM",
     *     description="Update Line",
     *     operationId="",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="line",
     *         in="formData",
     *         description="This requires the correct values to be passed.",
     *         required=true,
     *         type="array",
     *         @SWG\Items(
     *             type="string",
     *             description="Phone",
     *         ),
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
    $api->put('cucm/line', 'App\Http\Controllers\CucmLine@updateLine');
